<?php
declare(strict_types=1);

/**
 * Centralized product source used by both shop and homepage
 * Tries DB first, otherwise returns the local sample dataset.
 */
function loadDisplayProducts(?PDO $pdo = null): array
{
    if (!$pdo) {
        return getFallbackProducts();
    }

    try {
        $stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($products) ? $products : getFallbackProducts();
    } catch (Exception $e) {
        @error_log("Failed to load products from database: " . $e->getMessage());
        return getFallbackProducts();
    }
}

/**
 * Fallback products when database is not available
 */
function getFallbackProducts(): array
{
    return [
        [
            'id' => 1,
            'name' => 'Bovans Brown Layers',
            'slug' => 'bovans-brown-layers',
            'description' => 'Robust brown egg layers. Excellent feed conversion and egg quality.',
            'product_type' => 'live_chicken',
            'price' => 410,
            'stock_quantity' => 250,
            'image_url' => '/Frontend/images/layer-hens-eggs.jpg',
            'is_featured' => 1,
        ],
        [
            'id' => 2,
            'name' => 'Broiler Finisher (6-8 weeks)',
            'slug' => 'broiler-finisher-6-8-weeks',
            'description' => 'Final stage feed for broilers. High energy formula.',
            'product_type' => 'feed',
            'price' => 2900,
            'stock_quantity' => 180,
            'image_url' => '/Frontend/images/download (8).png',
            'is_featured' => 1,
        ],
        [
            'id' => 3,
            'name' => 'Busia Premium Mix',
            'slug' => 'busia-premium-mix',
            'description' => 'Our signature blend. Multi-purpose feed suitable for all poultry types.',
            'product_type' => 'feed',
            'price' => 3100,
            'stock_quantity' => 200,
            'image_url' => '/Frontend/images/kienyeji mash.png',
            'is_featured' => 1,
        ],
        [
            'id' => 4,
            'name' => 'Chick Mash (0-4 weeks)',
            'slug' => 'chick-mash-0-4-weeks',
            'description' => 'Fine mash feed for young chicks. Easy to digest with high energy content. 25kg bags.',
            'product_type' => 'feed',
            'price' => 1800,
            'stock_quantity' => 150,
            'image_url' => '/Frontend/images/Growers Mash.png',
            'is_featured' => 1,
        ],
        [
            'id' => 5,
            'name' => 'Chick Starter Crumbs (0-4 weeks)',
            'slug' => 'chick-starter-crumbs-0-4-weeks',
            'description' => 'High-protein formula for day-old chicks. 24% crude protein with vitamins and probiotics. 50kg bags.',
            'product_type' => 'feed',
            'price' => 3200,
            'stock_quantity' => 220,
            'image_url' => '/Frontend/images/Chick Starter Crumbs.png',
            'is_featured' => 1,
        ],
        [
            'id' => 6,
            'name' => 'Cobb 500 Broilers',
            'slug' => 'cobb-500-broilers',
            'description' => 'High-performance broilers with excellent feed conversion.',
            'product_type' => 'live_chicken',
            'price' => 480,
            'stock_quantity' => 400,
            'image_url' => '/Frontend/images/broiler-chickens.jpg',
            'is_featured' => 1,
        ],
        [
            'id' => 7,
            'name' => 'Day-Old Broiler Chicks',
            'slug' => 'day-old-broiler-chicks',
            'description' => 'Vaccinated broiler chicks. 95%+ hatch rate.',
            'product_type' => 'chicks',
            'price' => 80,
            'stock_quantity' => 2000,
            'image_url' => '/Frontend/images/day-old-chicks.jpg',
            'is_featured' => 1,
        ],
        [
            'id' => 8,
            'name' => 'Day-Old Layer Chicks',
            'slug' => 'day-old-layer-chicks',
            'description' => 'Premium layer chicks vaccinated and ready to grow.',
            'product_type' => 'chicks',
            'price' => 70,
            'stock_quantity' => 2000,
            'image_url' => '/Frontend/images/day-old-chicks.jpg',
            'is_featured' => 1,
        ],
        [
            'id' => 9,
            'name' => 'Farm-Fresh Grade-A Eggs (30-Egg Tray)',
            'slug' => 'farm-fresh-grade-a-eggs',
            'description' => 'Grade-A eggs harvested daily from modern layer houses — packed in 30-egg trays for homes, shops and hotels.',
            'product_type' => 'eggs',
            'price' => 420,
            'stock_quantity' => 500,
            'image_url' => '/Frontend/images/farm-fresh-eggs.jpg',
            'is_featured' => 1,
        ]
    ];
}

?>
