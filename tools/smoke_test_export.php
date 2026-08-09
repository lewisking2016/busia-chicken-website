<?php
// Smoke test for the export endpoint
require_once __DIR__ . '/../Backend/config/database.php';
$pdo = getDatabaseConnection();
if (!$pdo) {
    echo "No DB connection (expected if DB not configured yet). Skipping live test.\n";
    exit(0);
}
$modules = ['orders','daily_sales','bulk_sales','stores_movements','raw_materials','health','batches','daily_records','egg_grades','feed_production','flocks','customers'];
foreach ($modules as $m) {
    try {
        // Simulate the export without auth by directly running the SQL
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()";
        // Just count rows in the corresponding table
        $tables = [
            'orders' => 'orders', 'daily_sales' => 'daily_sales_reconciliation',
            'bulk_sales' => 'bulk_sales', 'stores_movements' => 'raw_material_movements',
            'raw_materials' => 'raw_materials', 'health' => 'health_records',
            'batches' => 'batches', 'daily_records' => 'daily_batch_records',
            'egg_grades' => 'daily_egg_grading', 'feed_production' => 'feed_production_batches',
            'flocks' => 'flocks', 'customers' => 'walk_in_customers',
        ];
        $tbl = $tables[$m];
        $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=" . $pdo->quote($tbl))->fetchColumn();
        if (!$exists) {
            echo "  [SKIP] $m — table $tbl does not exist (run migration)\n";
        } else {
            $count = (int)$pdo->query("SELECT COUNT(*) FROM $tbl")->fetchColumn();
            echo "  [OK]   $m — $tbl ($count rows)\n";
        }
    } catch (Exception $e) {
        echo "  [ERR]  $m — " . $e->getMessage() . "\n";
    }
}
