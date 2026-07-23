<?php
function renderFile($path) {
    ob_start();
    include $path;
    return ob_get_clean();
}

$indexHtml = renderFile(__DIR__ . '/../Frontend/index.php');
$shopHtml = renderFile(__DIR__ . '/../Frontend/pages/shop.php');

function extractProducts($html) {
    $products = [];
    // match product card blocks by looking for product-name and img
    if (preg_match_all('/<div class="product-card[\s\S]*?<img[^>]+src="([^"]+)"[^>]*>[\s\S]*?<h4[^>]*>(.*?)<\/h4>/i', $html, $m)) {
        foreach ($m[1] as $i => $src) {
            $name = strip_tags($m[2][$i]);
            $products[] = ['name' => trim($name), 'img' => $src];
        }
    }
    return $products;
}

$indexProducts = extractProducts($indexHtml);
$shopProducts = extractProducts($shopHtml);

echo "INDEX PRODUCTS:\n";
echo json_encode($indexProducts, JSON_PRETTY_PRINT);

echo "\n\nSHOP PRODUCTS:\n";
echo json_encode($shopProducts, JSON_PRETTY_PRINT);
