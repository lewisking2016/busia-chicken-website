<?php
/**
 * Central database schema manager (the single source of truth).
 *
 * WHY THIS FILE EXISTS
 * --------------------
 * Schema used to be spread over 8 .sql files of which the old
 * ensureBusiaSchema() only ran two, with every error silently swallowed.
 * The result on the live host was a database missing whole modules
 * (site_settings, website_sliders, testimonials, system_dropdowns, ...)
 * and an unhelpful "Base table or view not found: 1146" at save time.
 *
 * This runner fixes that structurally:
 *
 *   • ONE ordered list of migration sources  (busiaMigrationSources()).
 *   • EVERY statement is executed, and real failures are logged to
 *     logs/database.log instead of disappearing.
 *   • Statements are made idempotent at runtime: "ADD COLUMN IF NOT
 *     EXISTS" is pre-checked and normalised, so the same files run
 *     unchanged on MariaDB (cPanel default) and MySQL 8.
 *   • Progress is tracked in `schema_migrations` (per file) and
 *     `schema_state` (overall), so steady-state requests cost two
 *     cheap queries and never re-run work.
 *   • A verification pass compares the actual tables against
 *     busiaRequiredTables() and re-runs if anything is missing.
 *
 * Nothing here ever drops or rewrites existing data.
 */
declare(strict_types=1);

/**
 * Bump when a migration source file changes shape or a new required
 * table is added, so existing databases re-run on their next request.
 */
const BUSIA_SCHEMA_VERSION = 10;

if (!function_exists('tableExists')) {
    function tableExists(PDO $pdo, string $table): bool
    {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
            $stmt->execute([$table]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('columnExists')) {
    function columnExists(PDO $pdo, string $table, string $column): bool
    {
        try {
            $stmt = $pdo->prepare('SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1');
            $stmt->execute([$table, $column]);
            return $stmt->fetchColumn() !== false;
        } catch (Throwable $e) {
            return false;
        }
    }
}

/**
 * Structured database log. Written to logs/database.log (gitignored so it
 * never reaches the repository) and to the PHP error log for cPanel.
 */
function busiaDbLog(string $message, string $level = 'error'): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ' . $message;
    @error_log('[busia-db] ' . $message);

    static $logDir = null;
    if ($logDir === null) {
        $candidates = [dirname(__DIR__, 2) . '/logs', sys_get_temp_dir() . '/busia-logs'];
        foreach ($candidates as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            if (is_dir($dir) && is_writable($dir)) {
                $logDir = $dir;
                break;
            }
        }
        $logDir = $logDir ?? '';
    }
    if ($logDir !== '') {
        @file_put_contents($logDir . '/database.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Every schema source, in dependency order (base tables before the
 * modules that reference them).
 */
function busiaMigrationSources(): array
{
    return [
        'schema.sql',                       // core users / catalog / orders / flocks
        // The CMS tables come before settings.sql on purpose: settings.sql only
        // creates a minimal site_settings (key/value), while the canonical
        // definition here includes setting_group/description. Running this
        // first means site_settings is complete from the start.
        'migration_v5_cms.sql',             // site_settings/website_sliders/testimonials
        'settings.sql',                     // site_settings defaults
        'migration_poultry_complete.sql',   // batches, stores, egg & feed production
        'migration_v2.sql',                 // order pipeline, recurring feed orders
        'migration_v2_business.sql',        // costs, cashbook, credit, procurement, LPO
        'migration_v3.sql',                 // incoming stock, suppliers
        'migration_v4_dropdowns.sql',       // system_dropdowns + option seeds
        'migration_v6_legacy_tables.sql',   // production_history, stock_alerts, legacy view
        'migration_v7_cms.sql',             // cms_content, cms_faq_items (editable site content)
        'migration_v8_permissions.sql',     // user_permissions, message priority/read receipts
        'migration_v9_products.sql',        // real catalogue: eggs, layer feeds, organic manure & folia
        'migration_v10_content.sql',        // FAQ copy: eggs & feeds, no chick sales
    ];
}

/**
 * Tables that must exist for the application to be considered healthy.
 * Used by the verification pass and by tools/migrate.php + status.php.
 */
function busiaRequiredTables(): array
{
    return [
        // Core platform
        'users', 'categories', 'products', 'orders', 'order_items', 'mpesa_transactions',
        // Public site / CMS
        'site_settings', 'website_sliders', 'testimonials', 'settings',
        // Poultry operations
        'flocks', 'batches', 'production_records', 'financial_records',
        // Business modules
        'raw_materials', 'suppliers', 'purchase_orders', 'cashbook_entries',
        'customer_credits', 'role_permissions', 'system_dropdowns', 'activity_logs',
        // Feed / stock modules (previously created by no migration at all)
        'feed_recipes', 'feed_recipe_ingredients', 'production_history', 'stock_alerts',
        // Editable website content
        'cms_content', 'cms_faq_items',
        // Per-account access control & team messaging
        'user_permissions', 'messages', 'tasks', 'message_reads',
    ];
}

/**
 * Split a .sql file into individual statements.
 *
 * Quote-aware: semicolons inside string literals (seed text, notes) no
 * longer break the statement in half, and "--" / "#" / block comments are
 * removed outside of quotes. The previous split-on-";" approach silently
 * corrupted INSERT batches that contained punctuation.
 */
function busiaSplitSql(string $sql): array
{
    $statements = [];
    $buffer = '';
    $length = strlen($sql);
    $inSingle = $inDouble = $inBacktick = false;
    $inLineComment = $inBlockComment = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = ($i + 1 < $length) ? $sql[$i + 1] : '';

        if ($inLineComment) {
            if ($char === "\n") {
                $inLineComment = false;
                $buffer .= "\n";
            }
            continue;
        }
        if ($inBlockComment) {
            if ($char === '*' && $next === '/') {
                $inBlockComment = false;
                $i++;
            }
            continue;
        }
        if (!$inSingle && !$inDouble && !$inBacktick) {
            if ($char === '-' && $next === '-') { $inLineComment = true; $i++; continue; }
            if ($char === '#') { $inLineComment = true; continue; }
            if ($char === '/' && $next === '*') { $inBlockComment = true; $i++; continue; }
            if ($char === ';') {
                $trimmed = trim($buffer);
                if ($trimmed !== '') {
                    $statements[] = $trimmed;
                }
                $buffer = '';
                continue;
            }
        }

        if ($inSingle) {
            if ($char === '\\' && $next !== '') { $buffer .= $char . $next; $i++; continue; }
            if ($char === "'") { $inSingle = false; }
        } elseif ($inDouble) {
            if ($char === '\\' && $next !== '') { $buffer .= $char . $next; $i++; continue; }
            if ($char === '"') { $inDouble = false; }
        } elseif ($inBacktick) {
            if ($char === '`') { $inBacktick = false; }
        } else {
            if ($char === "'") { $inSingle = true; }
            elseif ($char === '"') { $inDouble = true; }
            elseif ($char === '`') { $inBacktick = true; }
        }

        $buffer .= $char;
    }

    $trimmed = trim($buffer);
    if ($trimmed !== '') {
        $statements[] = $trimmed;
    }
    return $statements;
}

/**
 * Split a comma-separated list at nesting depth 0 (used for multi-clause
 * ALTER TABLE statements), respecting quotes and parentheses.
 */
function busiaSplitTopLevel(string $list): array
{
    $parts = [];
    $buffer = '';
    $depth = 0;
    $length = strlen($list);
    $inSingle = $inDouble = $inBacktick = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $list[$i];
        if ($inSingle) {
            if ($char === '\\' && $i + 1 < $length) { $buffer .= $char . $list[++$i]; continue; }
            if ($char === "'") { $inSingle = false; }
        } elseif ($inDouble) {
            if ($char === '\\' && $i + 1 < $length) { $buffer .= $char . $list[++$i]; continue; }
            if ($char === '"') { $inDouble = false; }
        } elseif ($inBacktick) {
            if ($char === '`') { $inBacktick = false; }
        } else {
            if ($char === "'") { $inSingle = true; }
            elseif ($char === '"') { $inDouble = true; }
            elseif ($char === '`') { $inBacktick = true; }
            elseif ($char === '(') { $depth++; }
            elseif ($char === ')') { $depth = max(0, $depth - 1); }
            elseif ($char === ',' && $depth === 0) {
                $parts[] = trim($buffer);
                $buffer = '';
                continue;
            }
        }
        $buffer .= $char;
    }
    $trimmed = trim($buffer);
    if ($trimmed !== '') {
        $parts[] = $trimmed;
    }
    return $parts;
}

/**
 * 'BASE TABLE' or 'VIEW' (null when the object does not exist).
 */
function busiaTableType(PDO $pdo, string $name): ?string
{
    try {
        $stmt = $pdo->prepare('SELECT TABLE_TYPE FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1');
        $stmt->execute([$name]);
        $type = $stmt->fetchColumn();
        return $type === false ? null : (string)$type;
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * Does a constraint (PK / FK / UNIQUE / CHECK) with this name exist?
 */
function busiaConstraintExists(PDO $pdo, string $table, string $name): bool
{
    if ($name === '') {
        return false;
    }
    try {
        $stmt = $pdo->prepare('SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? LIMIT 1');
        $stmt->execute([$table, $name]);
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Does an index with this name exist?
 */
function busiaIndexExists(PDO $pdo, string $table, string $name): bool
{
    if ($name === '') {
        return false;
    }
    try {
        $stmt = $pdo->prepare('SELECT 1 FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1');
        $stmt->execute([$table, $name]);
        return (bool)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Normalise one ALTER TABLE clause. Returns the clause to run, or null when
 * it is already satisfied and can be skipped.
 */
function busiaNormaliseAlterClause(PDO $pdo, string $table, string $clause): ?string
{
    // ADD CONSTRAINT <name> ... — skip when that constraint (or an index with
    // the same name) is already there. Re-running an FK add either errors with
    // a duplicate constraint name or, worse, fails with errno 121.
    if (preg_match('/^ADD\s+CONSTRAINT\s+`?([A-Za-z0-9_$]+)`?/i', $clause, $cm)) {
        if (busiaConstraintExists($pdo, $table, $cm[1]) || busiaIndexExists($pdo, $table, $cm[1])) {
            return null;
        }
        return $clause;
    }

    // ADD [UNIQUE] INDEX|KEY <name> ...
    if (preg_match('/^ADD\s+(?:UNIQUE\s+|FULLTEXT\s+|SPATIAL\s+)?(?:INDEX|KEY)\s+`?([A-Za-z0-9_$]+)`?/i', $clause, $im)) {
        if (busiaIndexExists($pdo, $table, $im[1])) {
            return null;
        }
        return $clause;
    }

    if (preg_match('/^ADD\s+(?:COLUMN\s+)?(?:IF\s+NOT\s+EXISTS\s+)?`?([A-Za-z0-9_$]+)`?\s+/i', $clause, $m)) {
        $column = $m[1];
        // Not "ADD CONSTRAINT" / "ADD INDEX" — those are handled by error codes.
        if (!preg_match('/^ADD\s+(CONSTRAINT|INDEX|KEY|UNIQUE|PRIMARY|FOREIGN|FULLTEXT|SPATIAL)\b/i', $clause)) {
            if (columnExists($pdo, $table, $column)) {
                return null;
            }
            // MariaDB understands IF NOT EXISTS, MySQL 8 does not.
            return preg_replace('/^ADD\s+(?:COLUMN\s+)?IF\s+NOT\s+EXISTS\s+/i', 'ADD COLUMN ', $clause) ?? $clause;
        }
    }
    return $clause;
}

/**
 * Make an ALTER TABLE statement safe to re-run. Returns null to skip.
 */
function busiaPrepareStatement(PDO $pdo, string $statement, array &$notes, array &$deferred = []): ?string
{
    $statement = trim($statement);
    if ($statement === '') {
        return null;
    }

    // Never try to create or switch databases: the connection is already
    // scoped to the right one, and cPanel users cannot CREATE DATABASE.
    if (preg_match('/^CREATE\s+DATABASE\b/i', $statement) || preg_match('/^USE\s+/i', $statement)) {
        return null;
    }

    // Never replace a real table with a compatibility view. Some databases
    // still have the legacy `recipe_ingredients` table from an old install;
    // its data must win over the read-only view of the same name.
    if (preg_match('/^CREATE\s+(?:OR\s+REPLACE\s+)?VIEW\s+`?([A-Za-z0-9_$]+)`?/i', $statement, $vm)) {
        if (busiaTableType($pdo, $vm[1]) === 'BASE TABLE') {
            $notes[] = 'kept existing table instead of compatibility view: ' . $vm[1];
            return null;
        }
        return $statement;
    }

    if (!preg_match('/^ALTER\s+TABLE\s+`?([A-Za-z0-9_$]+)`?\s+(.+)$/is', $statement, $m)) {
        return $statement;
    }

    $table = $m[1];

    // Cannot ALTER a table that does not exist yet: another source file may
    // create it later in this run, so remember it and retry in a later pass.
    if (!tableExists($pdo, $table)) {
        $notes[] = "deferred — table {$table} does not exist yet";
        $deferred[] = $table;
        return null;
    }

    $clauses = busiaSplitTopLevel($m[2]);
    if (!$clauses) {
        return null;
    }

    $kept = [];
    foreach ($clauses as $clause) {
        $normalised = busiaNormaliseAlterClause($pdo, $table, $clause);
        if ($normalised === null) {
            $notes[] = "skip existing column on {$table}: " . substr($clause, 0, 60);
            continue;
        }
        $kept[] = $normalised;
    }

    if (!$kept) {
        return null;
    }
    return 'ALTER TABLE `' . $table . '` ' . implode(', ', $kept);
}

/**
 * MySQL error codes that mean "this migration step is already applied".
 * They are recorded as notes, never as failures.
 */
function busiaIgnorableSqlCodes(): array
{
    return [
        1007, // database exists
        1050, // table exists
        1060, // duplicate column name
        1061, // duplicate key name
        1068, // multiple primary key defined
        1091, // can't drop; doesn't exist
        1826, // duplicate foreign key constraint name
        121,  // duplicate key on write (duplicate FK/index name)
    ];
}

/**
 * Execute one statement, applying the idempotency normaliser.
 * Returns true when executed or already-applied, false when it failed.
 */
function busiaExecStatement(PDO $pdo, string $statement, array &$notes, array &$errors, string $source, array &$deferred = []): bool
{
    $prepared = busiaPrepareStatement($pdo, $statement, $notes, $deferred);
    if ($prepared === null) {
        return true;
    }

    try {
        $pdo->exec($prepared);
        return true;
    } catch (PDOException $e) {
        $code = (int)($e->errorInfo[1] ?? 0);
        if (in_array($code, busiaIgnorableSqlCodes(), true)) {
            $notes[] = "already applied (code {$code})";
            return true;
        }
        // Missing referenced table / FK ordering: retryable on the next pass.
        $errors[] = $source . ': ' . $e->getMessage() . ' | SQL: ' . substr($prepared, 0, 160);
        return false;
    } catch (Throwable $e) {
        $errors[] = $source . ': ' . $e->getMessage();
        return false;
    }
}

/**
 * Read (and create on first use) the two bookkeeping tables.
 */
function busiaMigrationLedger(PDO $pdo): bool
{
    try {
        $pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(190) NOT NULL UNIQUE,
            checksum CHAR(40) NOT NULL DEFAULT \'\',
            statements INT NOT NULL DEFAULT 0,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        $pdo->exec('CREATE TABLE IF NOT EXISTS schema_state (
            id TINYINT NOT NULL PRIMARY KEY,
            version INT NOT NULL DEFAULT 0,
            last_run DATETIME NULL,
            last_ok TINYINT NOT NULL DEFAULT 0,
            last_errors MEDIUMTEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        return true;
    } catch (Throwable $e) {
        busiaDbLog('Could not create schema bookkeeping tables: ' . $e->getMessage());
        return false;
    }
}

/**
 * Current schema state row (or null).
 */
function busiaSchemaStateGet(PDO $pdo): ?array
{
    try {
        $row = $pdo->query('SELECT version, last_run, last_ok, last_errors FROM schema_state WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function busiaSchemaStateSet(PDO $pdo, bool $ok, array $errors, array $missing): void
{
    try {
        $payload = json_encode([
            'errors' => array_slice($errors, 0, 20),
            'missing' => $missing,
        ], JSON_UNESCAPED_SLASHES);

        $stmt = $pdo->prepare('INSERT INTO schema_state (id, version, last_run, last_ok, last_errors)
            VALUES (1, ?, NOW(), ?, ?)
            ON DUPLICATE KEY UPDATE version = VALUES(version), last_run = VALUES(last_run),
                last_ok = VALUES(last_ok), last_errors = VALUES(last_errors)');
        $stmt->execute([BUSIA_SCHEMA_VERSION, $ok ? 1 : 0, $payload]);
    } catch (Throwable $e) {
        busiaDbLog('Could not persist schema state: ' . $e->getMessage());
    }
}

/**
 * Apply one migration file if it is new or changed.
 * Returns [applied(bool), notes[], errors[], deferredTables[]].
 */
function busiaApplyMigrationFile(PDO $pdo, string $file): array
{
    $path = __DIR__ . '/' . $file;
    $notes = [];
    $errors = [];
    $deferred = [];

    if (!is_file($path)) {
        $errors[] = $file . ': migration file missing';
        return [false, $notes, $errors, $deferred];
    }

    $sql = (string)@file_get_contents($path);
    $checksum = sha1($sql);

    $previous = null;
    try {
        $stmt = $pdo->prepare('SELECT checksum FROM schema_migrations WHERE filename = ?');
        $stmt->execute([$file]);
        $previous = $stmt->fetchColumn();
    } catch (Throwable $e) {
        // ledger unavailable — still run the file, it is idempotent
    }
    if ($previous !== false && $previous !== null && $previous === $checksum) {
        return [false, $notes, $errors, $deferred];
    }

    $statements = busiaSplitSql($sql);
    foreach ($statements as $statement) {
        busiaExecStatement($pdo, $statement, $notes, $errors, $file, $deferred);
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO schema_migrations (filename, checksum, statements)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE checksum = VALUES(checksum), statements = VALUES(statements), applied_at = NOW()');
        $stmt->execute([$file, $checksum, count($statements)]);
    } catch (Throwable $e) {
        busiaDbLog('Could not record migration ' . $file . ': ' . $e->getMessage());
    }

    return [true, $notes, $errors, $deferred];
}

/**
 * Compare the live database against the required table list.
 */
function busiaMissingTables(PDO $pdo): array
{
    try {
        $existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) ?: [];
    } catch (Throwable $e) {
        busiaDbLog('SHOW TABLES failed: ' . $e->getMessage());
        return busiaRequiredTables();
    }
    return array_values(array_diff(busiaRequiredTables(), $existing));
}

/**
 * Build the public report used by tools/migrate.php and status.php.
 */
function busiaSchemaReport(PDO $pdo): array
{
    $missing = busiaMissingTables($pdo);
    $state = busiaSchemaStateGet($pdo);
    $tableCount = 0;
    try {
        $tableCount = (int)$pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn();
    } catch (Throwable $e) {
        // leave at 0
    }

    return [
        'ok' => empty($missing),
        'missing' => $missing,
        'table_count' => $tableCount,
        'version' => BUSIA_SCHEMA_VERSION,
        'state' => $state,
    ];
}

/**
 * Run the whole schema. This is the only entry point the application needs.
 *
 * Options:
 *   force  (bool)   ignore the fast path and re-run every source
 *   post   (callable) receives PDO after migrations; used by auto_migrate.php
 *          for legacy reconciliation and master-data seeding
 */
function busiaMigrate(PDO $pdo, array $options = []): array
{
    $force = !empty($options['force']);
    $post = $options['post'] ?? null;

    $report = [
        'ok' => false,
        'applied' => [],
        'notes' => [],
        'errors' => [],
        'missing' => [],
        'table_count' => 0,
        'version' => BUSIA_SCHEMA_VERSION,
        'cached' => false,
    ];

    $ledgerReady = busiaMigrationLedger($pdo);
    $state = $ledgerReady ? busiaSchemaStateGet($pdo) : null;

    // ── Fast path ────────────────────────────────────────────────
    // Two cheap queries per request once the schema is healthy.
    if (!$force && $ledgerReady && $state
        && (int)$state['version'] === BUSIA_SCHEMA_VERSION
        && (int)$state['last_ok'] === 1) {
        $missing = busiaMissingTables($pdo);
        if (!$missing) {
            $report['ok'] = true;
            $report['cached'] = true;
            $report['table_count'] = count($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) ?: []);
            return $report;
        }
        busiaDbLog('Schema drift detected, repairing: ' . implode(', ', $missing), 'warning');
    }

    // ── Failure back-off ─────────────────────────────────────────
    // If the last attempt failed minutes ago, do not hammer the database
    // on every page load; admins can force a retry from tools/migrate.php.
    if (!$force && $ledgerReady && $state && (int)$state['last_ok'] === 0 && !empty($state['last_run'])) {
        $since = time() - (int)strtotime((string)$state['last_run']);
        if ($since >= 0 && $since < 600) {
            $decoded = json_decode((string)($state['last_errors'] ?? ''), true);
            $report['errors'] = is_array($decoded['errors'] ?? null) ? $decoded['errors'] : [];
            $report['missing'] = is_array($decoded['missing'] ?? null) ? $decoded['missing'] : busiaMissingTables($pdo);
            $report['cached'] = true;
            return $report;
        }
    }

    $allErrors = [];

    // If the ledger thinks everything ran but tables are still missing (a
    // statement failed on an earlier attempt, e.g. an FK target created
    // later), clear the ledger so the full set runs again. Only costs
    // anything when the schema is actually broken.
    if ($ledgerReady && busiaMissingTables($pdo)) {
        try {
            $pdo->exec('DELETE FROM schema_migrations');
        } catch (Throwable $e) {
            // keep going; files are idempotent anyway
        }
    }

    // Bring decade-old table shapes up to date BEFORE any source INSERTs run,
    // so a seed never fails on a column an older database is missing.
    busiaReconcileCoreColumns($pdo);

    // ── Apply sources, retrying so FK ordering resolves itself ───
    for ($pass = 0; $pass < 3; $pass++) {
        $passErrors = [];
        $deferredThisPass = [];
        $appliedThisPass = false;

        foreach (busiaMigrationSources() as $file) {
            [$applied, $notes, $errors, $deferred] = busiaApplyMigrationFile($pdo, $file);
            if ($applied) {
                $report['applied'][] = $file;
                $appliedThisPass = true;
            }
            foreach ($notes as $note) {
                $report['notes'][] = $file . ': ' . $note;
            }
            foreach ($errors as $error) {
                $passErrors[] = $error;
            }
            foreach ($deferred as $table) {
                $deferredThisPass[] = $table;
            }
        }

        $allErrors = $passErrors;

        // A source ALTERed a table another source creates later in the list.
        // If that table now exists, run the sources once more so the ALTER lands.
        $deferredRetry = false;
        foreach (array_unique($deferredThisPass) as $table) {
            if (tableExists($pdo, $table)) {
                $deferredRetry = true;
                break;
            }
        }

        if (!$passErrors && !$deferredRetry) {
            break;
        }
        if (!$appliedThisPass && !$deferredRetry && $pass > 0) {
            break; // no progress possible
        }
        // Force a re-run of every source on the next pass.
        try {
            $pdo->exec('DELETE FROM schema_migrations');
        } catch (Throwable $e) {
            break;
        }
    }

    // ── Column reconciliation for tables that predate this schema ─
    busiaReconcileCoreColumns($pdo);

    // ── Post-migration hook (legacy reconcile + master data seed) ─
    if (is_callable($post)) {
        try {
            $post($pdo);
        } catch (Throwable $e) {
            $allErrors[] = 'post-migration: ' . $e->getMessage();
            busiaDbLog('Post-migration step failed: ' . $e->getMessage());
        }
    }

    $report['missing'] = busiaMissingTables($pdo);
    $report['ok'] = empty($report['missing']);
    $report['errors'] = array_values(array_unique($allErrors));

    try {
        $report['table_count'] = (int)$pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn();
    } catch (Throwable $e) {
        // ignore
    }

    foreach ($report['errors'] as $error) {
        busiaDbLog($error);
    }
    if (!$report['ok']) {
        busiaDbLog('Schema incomplete after migration. Missing: ' . implode(', ', $report['missing']));
    }

    if ($ledgerReady) {
        busiaSchemaStateSet($pdo, $report['ok'], $report['errors'], $report['missing']);
    }

    return $report;
}

/**
 * Add columns that later code expects to tables created by an older shape.
 *
 * Example: settings.sql creates `site_settings` with only setting_key and
 * setting_value, while the CMS writes setting_group/description too — which
 * failed with "Unknown column 'setting_group'" on any database whose
 * site_settings came from settings.sql. Every entry is a no-op when present.
 */
function busiaReconcileCoreColumns(PDO $pdo): void
{
    $map = [
        'site_settings' => [
            'setting_group' => "ADD COLUMN setting_group VARCHAR(50) NOT NULL DEFAULT 'general'",
            'description'   => 'ADD COLUMN description VARCHAR(255) NULL',
        ],
        'website_sliders' => [
            'slide_order'   => 'ADD COLUMN slide_order INT NOT NULL DEFAULT 1',
            'is_active'     => 'ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1',
            'updated_at'    => 'ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ],
        'testimonials' => [
            'rating'        => 'ADD COLUMN rating INT DEFAULT 5',
            'is_approved'   => 'ADD COLUMN is_approved TINYINT(1) NOT NULL DEFAULT 0',
        ],
        'stock_alerts' => [
            'is_resolved'   => 'ADD COLUMN is_resolved TINYINT(1) NOT NULL DEFAULT 0',
            'message'       => 'ADD COLUMN message TEXT NULL',
        ],
        'production_history' => [
            'batch_number'  => 'ADD COLUMN batch_number VARCHAR(30) DEFAULT NULL',
            'produced_at'   => 'ADD COLUMN produced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'notes'         => 'ADD COLUMN notes TEXT NULL',
        ],
    ];

    foreach ($map as $table => $columns) {
        if (!tableExists($pdo, $table)) {
            continue;
        }
        foreach ($columns as $column => $definition) {
            if (columnExists($pdo, $table, $column)) {
                continue;
            }
            try {
                $pdo->exec('ALTER TABLE `' . $table . '` ' . $definition);
            } catch (Throwable $e) {
                busiaDbLog("Could not add {$table}.{$column}: " . $e->getMessage());
            }
        }
    }
}

/**
 * Seed the CMS content a brand-new database needs (hero slides + defaults).
 * Idempotent: seeds only when the table is empty, never overwrites edits.
 */
function busiaSeedCmsContent(PDO $pdo): void
{
    if (!tableExists($pdo, 'website_sliders')) {
        return;
    }

    try {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM website_sliders')->fetchColumn();
    } catch (Throwable $e) {
        return;
    }
    if ($count > 0) {
        return;
    }

    $slides = [
        [1, 'Welcome to Busia Chicken Farm', 'Quality Poultry for', 'East Africa',
            'Premium broilers, layers and day-old chicks — raised with care, delivered with confidence, and backed by a decade of sustainable farming excellence.',
            'Explore Products', '/Frontend/pages/shop.php', 'Our Story', '/Frontend/pages/about.php',
            '100', 'k', 'Chickens raised annually', '5000', '+', 'Happy customers', '13', '+', 'Years in business',
            '/Frontend/images/adbg.webp'],
        [2, 'The Egg People', 'Farm-Fresh Eggs,', 'Every Day',
            'State-of-the-art layer facilities producing the freshest, most nutritious eggs — harvested daily and delivered to your family or business.',
            'Order Fresh Eggs', '/Frontend/pages/shop.php?category=eggs', 'See Our Products', '/Frontend/pages/products.php',
            '96', '%', 'Eggs per bird per year', '100', '%', 'Biosafety compliant', '100', '%', 'Response time',
            '/Frontend/images/farm-fresh-eggs.webp'],
        [3, 'Expert Farm Management', 'Trusted by', 'Thousands',
            'From consulting, egg & meat production to premium feeds — our dedicated team helps farmers grow with confidence.',
            'Explore Services', '/Frontend/pages/services.php', 'Partner With Us', '/Frontend/pages/contact.php',
            '90', '%', 'Hatch success rate', '30', '%', 'Saved on feed costs', '4', ' Weeks', 'To market weight',
            '/Frontend/images/download-2.webp'],
    ];

    try {
        $stmt = $pdo->prepare('INSERT INTO website_sliders
            (slide_order, badge_text, title_main, title_em, subtitle,
             cta_primary_text, cta_primary_link, cta_secondary_text, cta_secondary_link,
             stat1_val, stat1_suffix, stat1_label, stat2_val, stat2_suffix, stat2_label,
             stat3_val, stat3_suffix, stat3_label, image_url, is_active)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)');
        foreach ($slides as $slide) {
            $stmt->execute($slide);
        }
    } catch (Throwable $e) {
        busiaDbLog('CMS seed failed: ' . $e->getMessage());
    }
}
