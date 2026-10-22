<?php
/**
 * inspect_live_schema.php — report what the REAL database has, so FK
 * mismatches can be identified instead of guessed at.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/Backend/config/database.php';

$pdo = getDatabaseConnection();
if (!$pdo) { fwrite(STDERR, "No DB connection\n"); exit(1); }

$db = $pdo->query('SELECT DATABASE()')->fetchColumn();
echo "Database: {$db}\n\n";

$targets = ['flocks', 'users', 'products', 'orders', 'houses', 'batches',
            'raw_materials', 'suppliers', 'walk_in_customers'];

echo "=== FK TARGET TABLES ===\n";
$existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($targets as $t) {
    $has = in_array($t, $existing, true);
    if (!$has) { printf("  %-20s MISSING\n", $t); continue; }
    $cols = $pdo->query("SHOW COLUMNS FROM `{$t}` WHERE Field='id'")->fetch(PDO::FETCH_ASSOC);
    $eng  = $pdo->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=" . $pdo->quote($t))->fetchColumn();
    printf("  %-20s id=%-8s null=%-4s engine=%s\n",
        $t, $cols['Type'] ?? '?', $cols['Null'] ?? '?', $eng ?: '?');
}

echo "\n=== ENGINE MIX (InnoDB required for FK) ===\n";
$engines = $pdo->query("SELECT ENGINE, COUNT(*) c FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() GROUP BY ENGINE")->fetchAll(PDO::FETCH_ASSOC);
foreach ($engines as $e) printf("  %-12s %d tables\n", $e['ENGINE'] ?: '(none)', (int)$e['c']);

echo "\n=== TABLES NEEDED BY THE NEW MODULES ===\n";
$need = ['houses','batches','daily_batch_records','health_records','egg_grades',
         'daily_egg_grading','daily_sales_reconciliation','daily_sales_lines',
         'raw_materials','raw_material_movements','feed_recipes','feed_recipe_ingredients',
         'feed_production_batches','walk_in_customers','bulk_sales','suppliers',
         'activity_logs','settings','batch_costs','cashbook_entries','customer_credits',
         'credit_payments','feeding_standards','feed_allocations','supplier_prices',
         'purchase_orders','purchase_order_items','broiler_weighings','hatchery_batches',
         'egg_losses','quality_tests','reorder_rules','system_alerts'];
$missing = array_diff($need, $existing);
printf("  present: %d / %d\n", count($need) - count($missing), count($need));
if ($missing) echo "  MISSING: " . implode(', ', $missing) . "\n";

echo "\n=== raw_materials COLUMNS THE CODE SELECTS ===\n";
if (in_array('raw_materials', $existing, true)) {
    $have = $pdo->query('SHOW COLUMNS FROM raw_materials')->fetchAll(PDO::FETCH_COLUMN);
    $want = ['material_name','material_code','unit','opening_balance','current_stock',
             'current_price_per_unit','category','min_stock_level','supplier_id','notes'];
    foreach ($want as $w) printf("  %-24s %s\n", $w, in_array($w, $have, true) ? 'ok' : 'MISSING');
}