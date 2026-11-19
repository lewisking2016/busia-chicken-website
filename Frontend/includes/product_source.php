<?php
declare(strict_types=1);

/**
 * Central product source for the shop and the homepage.
 *
 * Products live only in the `products` table and are managed in
 * Admin → Products. There is deliberately no hardcoded sample
 * catalogue: an empty table shows the shop's "no products" message
 * instead of inventing products the farm does not sell.
 */
function loadDisplayProducts(?PDO $pdo = null): array
{
    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->query('SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC');
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map('optimizeProductImagePath', $products ?: []);
    } catch (Throwable $e) {
        @error_log('Failed to load products from database: ' . $e->getMessage());
        return [];
    }
}

/**
 * Featured products for the homepage, straight from the table.
 */
function loadFeaturedProducts(?PDO $pdo = null, int $limit = 8): array
{
    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE is_active = 1 AND is_featured = 1 ORDER BY price DESC LIMIT ' . max(1, $limit));
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC LIMIT ' . max(1, $limit));
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return array_map('optimizeProductImagePath', $rows ?: []);
    } catch (Throwable $e) {
        @error_log('Failed to load featured products: ' . $e->getMessage());
        return [];
    }
}

function optimizeProductImagePath(array $product): array
{
    $url = $product['image_url'] ?? '';
    $path = parse_url((string)$url, PHP_URL_PATH);
    if (!is_string($path) || !str_starts_with($path, '/Frontend/images/')) return $product;

    $stem = pathinfo(rawurldecode(basename($path)), PATHINFO_FILENAME);
    $optimized = strtolower(str_replace(' ', '-', $stem)) . '.webp';
    if (is_file(__DIR__ . '/../images/' . $optimized)) {
        $product['image_url'] = '/Frontend/images/' . rawurlencode($optimized);
    }
    return $product;
}

/**
 * Kept for backwards compatibility: the site no longer ships sample
 * products, so this is always empty.
 */
function getFallbackProducts(): array
{
    return [];
}
