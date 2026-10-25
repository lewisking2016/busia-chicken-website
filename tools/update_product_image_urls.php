<?php
require_once __DIR__ . '/../Backend/config/database.php';
$pdo = getDatabaseConnection();

$map = [
    'feed' => '/Frontend/images/poultry-feed.jpg',
    'eggs' => '/Frontend/images/farm-fresh-eggs.jpg',
    'chicks' => '/Frontend/images/day-old-chicks.jpg',
    'live_chicken' => '/Frontend/images/broiler-chickens.jpg',
];

// Find products with null or empty image_url
$stmt = $pdo->query("SELECT id, product_type, slug, name FROM products WHERE image_url IS NULL OR image_url = ''");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    echo "No products to update.\n";
    exit(0);
}

$update = $pdo->prepare("UPDATE products SET image_url = :url WHERE id = :id");
foreach ($rows as $r) {
    $type = $r['product_type'] ?? 'feed';
    $url = $map[$type] ?? '/Frontend/images/Chick Starter Crumbs.png';
    $update->execute([':url' => $url, ':id' => $r['id']]);
    echo "Updated product id {$r['id']} ({$r['name']}) -> $url\n";
}

echo "Done.\n";
