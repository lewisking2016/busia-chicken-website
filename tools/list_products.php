<?php
require __DIR__ . '/../Backend/config/database.php';
$db = getDatabaseConnection();
$stmt = $db->query("SELECT id, name, price, product_type, stock_quantity FROM products");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total products: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "ID {$r['id']}: {$r['name']} - KES {$r['price']} ({$r['product_type']})\n";
}
