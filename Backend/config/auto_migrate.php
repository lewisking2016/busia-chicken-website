<?php
/**
 * Auto-Migration — ensures all module tables exist on every connection.
 *
 * Safe to call repeatedly: CREATE TABLE IF NOT EXISTS makes re-runs no-ops,
 * and individual statement errors are skipped. The completeness guard below
 * re-runs a migration file whenever any of its tables is missing, so tables
 * added to the migration files later are created automatically.
 */
declare(strict_types=1);

/**
 * Split a migration .sql file into individual executable statements.
 *
 * Comment banner lines ("-- ...") are stripped BEFORE splitting. The old
 * approach split on ";" and then dropped chunks that STARTED with a comment —
 * which silently discarded every CREATE TABLE that followed a banner, leaving
 * tables like broiler_weighings / egg_losses permanently missing.
 */
function splitMigrationSql(string $sql): array
{
    // Remove full-line SQL comments (these files use "-- ..." banners)
    $lines = preg_split('/\R/', $sql);
    if ($lines !== false) {
        $lines = array_filter($lines, function (string $line): bool {
            $trimmed = ltrim($line);
            return $trimmed !== '' && !str_starts_with($trimmed, '--');
        });
        $sql = implode("\n", $lines);
    }

    // Strip any USE statement (we are already connected to the right DB)
    $sql = preg_replace('/USE\s+`?\w+`?\s*;/i', '', $sql);

    $statements = [];
    foreach (array_map('trim', explode(';', $sql)) as $stmt) {
        if ($stmt !== '') {
            $statements[] = $stmt;
        }
    }
    return $statements;
}

/**
 * Execute a migration file, skipping statements that fail (idempotent).
 */
function runMigrationFile(PDO $pdo, string $file): void
{
    $sql = @file_get_contents($file);
    if ($sql === false) {
        return;
    }
    foreach (splitMigrationSql($sql) as $stmt) {
        try {
            $pdo->exec($stmt);
        } catch (Exception $e) {
            // Ignore — already applied, or a transient FK ordering issue.
            // The completeness guard below re-runs on the next request.
        }
    }
}

/**
 * Table names created by a migration file, derived from the file itself.
 */
function migrationTableNames(string $file): array
{
    $sql = @file_get_contents($file);
    if ($sql === false) {
        return [];
    }
    preg_match_all('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`?(\w+)`?/i', $sql, $m);
    return array_values(array_unique($m[1] ?? []));
}

/**
 * Reconcile legacy column shapes with the schema the code expects.
 *
 * The raw_materials / suppliers tables in older databases use an older shape
 * (name, stock_tons, current_price_per_ton, feed_type) while the migration
 * files and every current module read (material_name, current_stock,
 * current_price_per_unit, unit, category) and (supplier_name). Adding columns
 * and back-filling from the legacy ones lets both old and new code read the
 * table, without touching or dropping any existing data. Idempotent.
 */
function reconcileLegacySchema(PDO $pdo): void
{
    // ── raw_materials ──
    if (tableExists($pdo, 'raw_materials')) {
        $add = [];
        if (!columnExists($pdo, 'raw_materials', 'material_name'))   $add[] = 'ADD COLUMN material_name VARCHAR(100) NULL AFTER id';
        if (!columnExists($pdo, 'raw_materials', 'material_code'))   $add[] = 'ADD COLUMN material_code VARCHAR(50) NULL';
        if (!columnExists($pdo, 'raw_materials', 'unit'))            $add[] = "ADD COLUMN unit VARCHAR(20) NOT NULL DEFAULT 'kg'";
        if (!columnExists($pdo, 'raw_materials', 'opening_balance')) $add[] = 'ADD COLUMN opening_balance DECIMAL(12,3) NOT NULL DEFAULT 0';
        if (!columnExists($pdo, 'raw_materials', 'current_stock'))   $add[] = 'ADD COLUMN current_stock DECIMAL(12,3) NOT NULL DEFAULT 0';
        if (!columnExists($pdo, 'raw_materials', 'current_price_per_unit')) $add[] = 'ADD COLUMN current_price_per_unit DECIMAL(10,2) NOT NULL DEFAULT 0';
        if (!columnExists($pdo, 'raw_materials', 'category'))        $add[] = "ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'feed_ingredient'";
        if (!columnExists($pdo, 'raw_materials', 'supplier_id'))     $add[] = 'ADD COLUMN supplier_id INT NULL';
        if (!columnExists($pdo, 'raw_materials', 'notes'))           $add[] = 'ADD COLUMN notes TEXT NULL';
        if ($add) {
            $pdo->exec('ALTER TABLE raw_materials ' . implode(', ', $add));
        }

        // Back-fill from the legacy columns when they exist. The legacy stock
        // is stored in TONS (old code converts kg -> tons), the current schema
        // is in KG — so convert 1:1000 and price per ton -> per kg.
        if (columnExists($pdo, 'raw_materials', 'name')) {
            $pdo->exec("UPDATE raw_materials SET material_name = name WHERE material_name IS NULL OR material_name = ''");
            if (columnExists($pdo, 'raw_materials', 'stock_tons')) {
                $pdo->exec('UPDATE raw_materials SET current_stock = stock_tons * 1000, opening_balance = stock_tons * 1000 WHERE stock_tons IS NOT NULL AND (current_stock = 0 OR current_stock IS NULL)');
            }
            if (columnExists($pdo, 'raw_materials', 'current_price_per_ton')) {
                $pdo->exec('UPDATE raw_materials SET current_price_per_unit = current_price_per_ton / 1000 WHERE current_price_per_ton IS NOT NULL AND (current_price_per_unit = 0 OR current_price_per_unit IS NULL)');
            }
        }
    }

    // ── suppliers ──
    if (tableExists($pdo, 'suppliers')
        && !columnExists($pdo, 'suppliers', 'supplier_name')
        && columnExists($pdo, 'suppliers', 'name')) {
        $pdo->exec('ALTER TABLE suppliers ADD COLUMN supplier_name VARCHAR(150) NULL AFTER id');
        $pdo->exec("UPDATE suppliers SET supplier_name = name WHERE supplier_name IS NULL OR supplier_name = ''");
    }

    // ── financial_records (expenses) ──
    if (tableExists($pdo, 'financial_records')) {
        $add = [];
        if (!columnExists($pdo, 'financial_records', 'payment_method')) {
            $add[] = "ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash'";
        }
        if (!columnExists($pdo, 'financial_records', 'payment_status')) {
            $add[] = "ADD COLUMN payment_status ENUM('Pending','Approved','Failed','Completed') DEFAULT 'Completed'";
        }
        if ($add) {
            $pdo->exec('ALTER TABLE financial_records ' . implode(', ', $add));
        }
    }
}

/**
 * Ensure all module tables exist. No-op when everything is present.
 */
function ensureBusiaSchema(PDO $pdo): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        // Always reconcile legacy column shapes first (idempotent, cheap) so
        // existing databases get the columns the current modules read even
        // when every table already exists.
        reconcileLegacySchema($pdo);

        $configDir = __DIR__;
        $poultryFile = $configDir . '/migration_poultry_complete.sql';
        $businessFile = $configDir . '/migration_v2_business.sql';

        // Loop until stable: a statement can fail mid-run when its foreign
        // key target is created later in the same pass (e.g. batches depends
        // on houses/flocks). Later passes create those, then the dependents.
        for ($pass = 0; $pass < 6; $pass++) {
            $existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $missingPoultry = array_diff(migrationTableNames($poultryFile), $existing);
            $missingBusiness = array_diff(migrationTableNames($businessFile), $existing);

            if (!$missingPoultry && !$missingBusiness) {
                return; // everything present
            }

            $tableCountBefore = count($existing);

            // Order matters: the business tables FK-reference poultry tables
            // (batches, raw_materials, suppliers, walk_in_customers).
            if ($missingPoultry) {
                runMigrationFile($pdo, $poultryFile);
            }
            if ($missingBusiness) {
                runMigrationFile($pdo, $businessFile);
            }

            $after = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            if (count($after) <= $tableCountBefore) {
                return; // no progress — give up quietly, retried next request
            }
        }
    } catch (Exception $e) {
        // Silent — never break the page
    }
}
