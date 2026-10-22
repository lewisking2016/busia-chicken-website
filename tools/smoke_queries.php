<?php
/**
 * smoke_queries.php — run the real SELECTs each admin module issues, so we
 * catch missing tables/columns before a user does. Read-only.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/Backend/config/database.php';

$pdo = getDatabaseConnection();
if (!$pdo) { fwrite(STDERR, "No DB connection\n"); exit(1); }

echo "DB: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n\n";

$checks = [
    'Batches list'          => 'SELECT b.*, h.house_name FROM batches b LEFT JOIN houses h ON h.id=b.house_id ORDER BY b.placement_date DESC LIMIT 5',
    'Daily batch records'   => 'SELECT * FROM daily_batch_records ORDER BY record_date DESC LIMIT 5',
    'Health records'        => 'SELECT h.*, b.batch_name, f.flock_name FROM health_records h LEFT JOIN batches b ON b.id=h.batch_id LEFT JOIN flocks f ON f.id=h.flock_id ORDER BY h.record_date DESC LIMIT 5',
    'Egg grades'            => 'SELECT * FROM egg_grades ORDER BY weight_min_grams DESC',
    'Daily sales recon'     => 'SELECT * FROM daily_sales_reconciliation ORDER BY sale_date DESC LIMIT 5',
    'Raw materials'         => 'SELECT * FROM raw_materials ORDER BY category, material_name',
    'Feed recipes'          => 'SELECT * FROM feed_recipes ORDER BY recipe_name',
    'Feed production'       => 'SELECT p.*, r.recipe_name FROM feed_production_batches p LEFT JOIN feed_recipes r ON r.id=p.recipe_id ORDER BY p.production_date DESC LIMIT 5',
    'Walk-in customers'     => 'SELECT * FROM walk_in_customers ORDER BY customer_name LIMIT 5',
    'Bulk sales'            => 'SELECT * FROM bulk_sales ORDER BY sale_date DESC LIMIT 5',
    'Suppliers'             => 'SELECT * FROM suppliers ORDER BY supplier_name',
    'Batch costs'           => 'SELECT c.*, b.batch_name FROM batch_costs c LEFT JOIN batches b ON b.id=c.batch_id ORDER BY c.cost_date DESC LIMIT 5',
    'Cashbook'              => 'SELECT * FROM cashbook_entries ORDER BY entry_date DESC LIMIT 5',
    'Customer credits'      => 'SELECT * FROM customer_credits ORDER BY credit_date DESC LIMIT 5',
    'Feeding standards'     => 'SELECT * FROM feeding_standards ORDER BY bird_type, week_number LIMIT 5',
    'Feed allocations'      => 'SELECT * FROM feed_allocations ORDER BY allocation_date DESC LIMIT 5',
    'Purchase orders'       => 'SELECT po.*, s.supplier_name FROM purchase_orders po LEFT JOIN suppliers s ON s.id=po.supplier_id ORDER BY po.order_date DESC LIMIT 5',
    'Broiler weighings'     => 'SELECT w.*, b.batch_name FROM broiler_weighings w LEFT JOIN batches b ON b.id=w.batch_id ORDER BY w.weigh_date DESC LIMIT 5',
    'Hatchery'              => 'SELECT * FROM hatchery_batches ORDER BY setting_date DESC LIMIT 5',
    'Egg losses'            => 'SELECT * FROM egg_losses ORDER BY loss_date DESC LIMIT 5',
    'Quality tests'         => 'SELECT q.*, r.material_name FROM quality_tests q LEFT JOIN raw_materials r ON r.id=q.material_id ORDER BY q.test_date DESC LIMIT 5',
    'Analytics: production' => "SELECT record_date day, SUM(total_eggs) eggs, SUM(mortality) mortality FROM daily_batch_records WHERE record_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY record_date ORDER BY record_date",
    'Analytics: profit'     => "SELECT DATE_FORMAT(entry_date,'%Y-%m') m, SUM(amount) t FROM cashbook_entries WHERE direction='in' GROUP BY 1 ORDER BY 1",
    'Analytics: credit'     => "SELECT SUM(CASE WHEN status IN ('unpaid','partial') THEN balance ELSE 0 END) owed FROM customer_credits",
];

$pass = 0; $fail = 0;
foreach ($checks as $label => $sql) {
    try {
        $rows = $pdo->query($sql)->fetchAll();
        printf("  [ok]   %-24s %d rows\n", $label, count($rows));
        $pass++;
    } catch (Exception $e) {
        printf("  [FAIL] %-24s %s\n", $label, $e->getMessage());
        $fail++;
    }
}

echo "\n{$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);