<?php
require_once __DIR__ . '/../Backend/config/database.php';
$pdo = getDatabaseConnection();

$map = [
    'feed' => '/Frontend/images/Growers Mash.png',
    'eggs' => '/Frontend/images/download (3).png',
    'chicks' => '/Frontend/images/download (7).png',
    'live_chicken' => '/Frontend/images/download (4).png',
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
