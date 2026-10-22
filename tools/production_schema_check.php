<?php
/**
 * Production schema check — READ-ONLY. Safe to run on the live host.
 *
 * Usage (from the project root):
 *   php tools/production_schema_check.php
 *
 * What it does:
 *   - Reads the DB credentials the app itself uses (Backend/config/database.php)
 *   - Reports which required tables/columns are present or missing
 *   - Reports whether the connected user can CREATE/ALTER (migration capability)
 *   - Changes NOTHING. Use it to decide whether to deploy + self-heal,
 *     or to import the SQL migrations manually via phpMyAdmin.
 *
 * Exit codes:
 *   0 = schema complete
 *   1 = missing tables/columns (self-heal will create them on first page load)
 *   2 = could not connect
 */

declare(strict_types=1);

$root = dirname(__DIR__);

// ── Use the app's own resolved credentials so this checks the REAL database ──
// database.php resolves credentials (local file → env → defaults) and defines
// DB_HOST/DB_NAME/DB_USER/DB_PASS. Including it here is the only reliable way
// to see the values the live site actually connects with.
$configFile = $root . '/Backend/config/database.php';
if (!is_readable($configFile)) {
    fwrite(STDERR, "Cannot read {$configFile}\n");
    exit(2);
}

require_once $configFile;

$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$name = defined('DB_NAME') ? DB_NAME : '';
$user = defined('DB_USER') ? DB_USER : '';
$pass = defined('DB_PASS') ? DB_PASS : '';

if ($name === '') {
    fwrite(STDERR, "Could not determine DB_NAME from database.php\n");
    exit(2);
}

echo "Target database: {$name} @ {$host}\n\n";

try {
    $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, 'Connection failed: ' . $e->getMessage() . "\n");
    exit(2);
}

$existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) ?: [];

// Reuse the app's own table-name extraction so this stays in sync with it.
require_once $root . '/Backend/config/auto_migrate.php';

$required = [];
foreach (['migration_poultry_complete.sql', 'migration_v2_business.sql'] as $sqlFile) {
    $path = $root . '/Backend/config/' . $sqlFile;
    if (is_readable($path) && function_exists('migrationTableNames')) {
        $required = array_merge($required, migrationTableNames($path));
    }
}
$required = array_values(array_unique($required));
sort($required);

$missing = array_values(array_diff($required, $existing));

echo "Tables found:   " . count($existing) . "\n";
echo "Tables required: " . count($required) . "\n";

if ($missing) {
    echo "\nMISSING TABLES (" . count($missing) . "):\n";
    foreach ($missing as $t) {
        echo "  - {$t}\n";
    }
} else {
    echo "\nAll required tables present.\n";
}

// Spot-check columns the app sorts/queries by directly.
$columnChecks = [
    'raw_materials' => ['category', 'unit', 'current_stock', 'current_price_per_unit'],
];
$missingCols = [];
foreach ($columnChecks as $table => $cols) {
    if (!in_array($table, $existing, true)) {
        continue; // table itself reported above
    }
    $have = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    foreach ($cols as $c) {
        if (!in_array($c, $have, true)) {
            $missingCols[] = "{$table}.{$c}";
        }
    }
}

if ($missingCols) {
    echo "\nMISSING COLUMNS (" . count($missingCols) . "):\n";
    foreach ($missingCols as $c) {
        echo "  - {$c}\n";
    }
} else {
    echo "All spot-checked columns present.\n";
}

// Can this DB user run migrations on its own?
echo "\nMigration capability:\n";
foreach ([
    'CREATE TABLE' => 'CREATE TABLE',
    'ALTER TABLE'  => 'ALTER TABLE `__busia_probe__` ADD COLUMN `x` INT',
] as $label => $probe) {
    try {
        if ($label === 'CREATE TABLE') {
            $pdo->exec('CREATE TABLE IF NOT EXISTS `__busia_probe__` (`id` INT)');
            $pdo->exec('DROP TABLE IF EXISTS `__busia_probe__`');
            echo "  [yes] {$label}\n";
        } else {
            // Probe ALTER on a throwaway table, then clean up.
            $pdo->exec('CREATE TABLE IF NOT EXISTS `__busia_probe__` (`id` INT)');
            $pdo->exec('ALTER TABLE `__busia_probe__` ADD COLUMN `x` INT');
            $pdo->exec('DROP TABLE IF EXISTS `__busia_probe__`');
            echo "  [yes] {$label}\n";
        }
    } catch (PDOException $e) {
        echo "  [NO ] {$label} — auto-migration will not work on this host\n";
        echo "         -> Import Backend/config/migration_*.sql via phpMyAdmin instead\n";
    }
}

echo "\n---\n";
if ($missing || $missingCols) {
    echo "VERDICT: incomplete. Deploy the updated code and load any page once;\n";
    echo "         the self-healer will create what is listed above.\n";
    exit(1);
}
echo "VERDICT: schema complete.\n";
exit(0);