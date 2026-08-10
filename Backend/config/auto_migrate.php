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
 * Ensure all module tables exist. No-op when everything is present.
 */
function ensureBusiaSchema(PDO $pdo): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
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
