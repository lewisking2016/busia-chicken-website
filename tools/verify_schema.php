<?php
/**
 * verify_schema.php — proves ensureBusiaSchema() creates every table the
 * modules need, from an EMPTY database. Safe: it creates/drops only a
 * scratch database named busia_schema_probe.
 *
 * Usage: php tools/verify_schema.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);

// Point at the real credentials but a throwaway database.
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$name = getenv('DB_NAME') ?: 'busia_chicken_db';
$sock = null;

try {
    $probe = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Cannot connect to MySQL: " . $e->getMessage() . "\n");
    exit(1);
}

$scratch = 'busia_schema_probe';
$probe->exec("DROP DATABASE IF EXISTS `{$scratch}`");
$probe->exec("CREATE DATABASE `{$scratch}`");
echo "Created scratch database `{$scratch}`\n\n";

// Load the migration helper FIRST (it no longer pulls in database.php, so this
// is safe and avoids the circular-include trap the app boot path would hit).
require_once $root . '/Backend/config/auto_migrate.php';

$pdo = new PDO("mysql:host={$host};dbname={$scratch};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// The base schema (users/flocks/products/orders...) the migrations FK into.
$base = file_get_contents($root . '/Backend/config/schema.sql');
$base = preg_replace('/USE\s+`?\w+`?\s*;/i', '', (string)$base);
$base = preg_replace('/DROP\s+TABLE[^;]*;/i', '', $base);
foreach (array_filter(array_map('trim', explode(';', (string)$base))) as $stmt) {
    if ($stmt === '' || str_starts_with($stmt, '--')) continue;
    try { $pdo->exec($stmt); } catch (Exception $e) { /* optional */ }
}
echo "Base schema loaded.\n";

// Now run the thing under test.
ensureBusiaSchema($pdo);

$existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

$required = [
    // poultry_complete
    'houses', 'batches', 'daily_batch_records', 'health_records',
    'egg_grades', 'daily_egg_grading', 'daily_sales_reconciliation',
    'daily_sales_lines', 'raw_materials', 'raw_material_movements',
    'feed_recipes', 'feed_recipe_ingredients', 'feed_production_batches',
    'walk_in_customers', 'bulk_sales', 'suppliers', 'activity_logs', 'settings',
    // v2_business
    'batch_costs', 'cashbook_entries', 'customer_credits', 'credit_payments',
    'feeding_standards', 'feed_allocations', 'supplier_prices',
    'purchase_orders', 'purchase_order_items', 'broiler_weighings',
    'hatchery_batches', 'egg_losses', 'quality_tests', 'reorder_rules',
    'system_alerts',
];

$missing = array_diff($required, $existing);

echo "\nRequired tables: " . count($required) . "\n";
echo "Present:        " . count(array_intersect($required, $existing)) . "\n";
echo "Missing:        " . count($missing) . "\n";

if ($missing) {
    echo "\nMISSING:\n - " . implode("\n - ", $missing) . "\n";
}

// Spot-check that the raw_materials columns the modules SELECT exist.
$wantCols = ['material_name','material_code','unit','opening_balance','current_stock',
             'current_price_per_unit','category','min_stock_level','supplier_id','notes'];
$haveCols = $pdo->query('SHOW COLUMNS FROM raw_materials')->fetchAll(PDO::FETCH_COLUMN);
$missingCols = array_diff($wantCols, $haveCols);
echo "\nraw_materials columns missing: " . (count($missingCols) ? implode(', ', $missingCols) : 'none') . "\n";

// Confirm seed data landed.
foreach (['egg_grades','feeding_standards','settings'] as $t) {
    if (in_array($t, $existing, true)) {
        $n = $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo "seed rows in {$t}: {$n}\n";
    }
}

$probe->exec("DROP DATABASE IF EXISTS `{$scratch}`");
echo "\nScratch database dropped.\n";

exit(empty($missing) && empty($missingCols) ? 0 : 1);