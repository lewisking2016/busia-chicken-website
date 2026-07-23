<?php
require_once __DIR__ . '/../Frontend/includes/product_source.php';
require_once __DIR__ . '/../Frontend/includes/config.php';
$pdo = getDB();
$products = loadDisplayProducts($pdo);
echo json_encode(array_slice($products,0,8), JSON_PRETTY_PRINT);
