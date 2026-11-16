<?php
require __DIR__ . '/../Backend/config/database.php';
$db = getDatabaseConnection();

$featured = [
    [
        'name' => 'Bovans Brown Layers',
        'slug' => 'bovans-brown-layers',
        'desc' => 'Robust brown egg layers. Excellent feed conversion and egg quality.',
        'price' => 410.00,
        'type' => 'live_chicken',
        'category_id' => 2,
        'stock' => 250,
        'img' => '/Frontend/images/layer-hens-eggs.jpg',
        'featured' => 1
    ],
    [
        'name' => 'Broiler Finisher (6-8 weeks)',
        'slug' => 'broiler-finisher-6-8-weeks',
        'desc' => 'Final stage feed for broilers. High energy formula.',
        'price' => 2900.00,
        'type' => 'feed',
        'category_id' => 4,
        'stock' => 180,
        'img' => '/Frontend/images/download (8).png',
        'featured' => 1
    ],
    [
        'name' => 'Busia Premium Mix',
        'slug' => 'busia-premium-mix',
        'desc' => 'Our signature blend. Multi-purpose feed suitable for all poultry types.',
        'price' => 3100.00,
        'type' => 'feed',
        'category_id' => 4,
        'stock' => 200,
        'img' => '/Frontend/images/kienyeji mash.png',
        'featured' => 1
    ],
    [
        'name' => 'Chick Mash (0-4 weeks)',
        'slug' => 'chick-mash-0-4-weeks',
        'desc' => 'Fine mash feed for young chicks. Easy to digest with high energy content. 25kg bags.',
        'price' => 1800.00,
        'type' => 'feed',
        'category_id' => 4,
        'stock' => 150,
        'img' => '/Frontend/images/Growers Mash.png',
        'featured' => 1
    ],
    [
        'name' => 'Chick Starter Crumbs (0-4 weeks)',
        'slug' => 'chick-starter-crumbs-0-4-weeks',
        'desc' => 'High-protein formula for day-old chicks. 24% crude protein with vitamins and probiotics. 50kg bags.',
        'price' => 3200.00,
        'type' => 'feed',
        'category_id' => 4,
        'stock' => 220,
        'img' => '/Frontend/images/Chick Starter Crumbs.png',
        'featured' => 1
    ],
    [
        'name' => 'Cobb 500 Broilers',
        'slug' => 'cobb-500-broilers',
        'desc' => 'High-performance broilers with excellent feed conversion.',
        'price' => 480.00,
        'type' => 'live_chicken',
        'category_id' => 1,
        'stock' => 400,
        'img' => '/Frontend/images/broiler-chickens.jpg',
        'featured' => 1
    ],
    [
        'name' => 'Day-Old Broiler Chicks',
        'slug' => 'day-old-broiler-chicks',
        'desc' => 'Vaccinated broiler chicks. 95%+ hatch rate.',
        'price' => 80.00,
        'type' => 'chicks',
        'category_id' => 3,
        'stock' => 2000,
        'img' => '/Frontend/images/day-old-chicks.jpg',
        'featured' => 1
    ],
    [
        'name' => 'Day-Old Layer Chicks',
        'slug' => 'day-old-layer-chicks',
        'desc' => 'Premium layer chicks vaccinated and ready to grow.',
        'price' => 70.00,
        'type' => 'chicks',
        'category_id' => 3,
        'stock' => 2000,
        'img' => '/Frontend/images/day-old-chicks.jpg',
        'featured' => 1
    ],
    [
        'name' => 'Farm-Fresh Grade-A Eggs (30-Egg Tray)',
        'slug' => 'farm-fresh-grade-a-eggs',
        'desc' => 'Grade-A eggs harvested daily from modern layer facilities — packed in 30-egg trays for homes, shops and hotels.',
        'price' => 420.00,
        'type' => 'eggs',
        'category_id' => 2,
        'stock' => 500,
        'img' => '/Frontend/images/farm-fresh-eggs.jpg',
        'featured' => 1
    ],
    [
        'name' => 'Organic Poultry Manure (50kg)',
        'slug' => 'organic-poultry-manure-50kg',
        'desc' => 'Rich organic fertilizer produced from bird droppings, improving soil structure and crop yield.',
        'price' => 450.00,
        'type' => 'feed',
        'category_id' => 4,
        'stock' => 300,
        'img' => '/Frontend/images/poultry-feed.jpg',
        'featured' => 0
    ],
    [
        'name' => 'Organic Folia Fertilizer (5L)',
        'slug' => 'organic-folia-fertilizer-5l',
        'desc' => 'Natural organic foliar feed designed to support vegetative growth and fruit set in vegetables and crops.',
        'price' => 1200.00,
        'type' => 'feed',
        'category_id' => 4,
        'stock' => 150,
        'img' => '/Frontend/images/adbg.png',
        'featured' => 0
    ]
];

foreach ($featured as $p) {
    $stmt = $db->prepare("SELECT id FROM products WHERE name = ? OR slug = ?");
    $stmt->execute([$p['name'], $p['slug']]);
    $id = $stmt->fetchColumn();

    if ($id) {
        $update = $db->prepare("UPDATE products SET 
            name = ?,
            price = ?, 
            description = ?, 
            product_type = ?, 
            category_id = ?,
            image_url = ?, 
            is_featured = ?, 
            stock_quantity = GREATEST(stock_quantity, ?),
            is_active = 1
            WHERE id = ?");
        $update->execute([$p['name'], $p['price'], $p['desc'], $p['type'], $p['category_id'], $p['img'], $p['featured'], $p['stock'], $id]);
        echo "Updated: {$p['name']} (ID: $id)\n";
    } else {
        $insert = $db->prepare("INSERT INTO products 
            (name, slug, description, price, product_type, category_id, image_url, is_featured, stock_quantity, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $insert->execute([$p['name'], $p['slug'], $p['desc'], $p['price'], $p['type'], $p['category_id'], $p['img'], $p['featured'], $p['stock']]);
        echo "Inserted: {$p['name']}\n";
    }
}

// Rename Wangari Premium Mix if exists
$db->exec("UPDATE products SET name = 'Busia Premium Mix', slug = 'busia-premium-mix', description = 'Our signature blend. Multi-purpose feed suitable for all poultry types.', price = 3100.00 WHERE name LIKE '%Wangari%'");

echo "Product sync completed successfully!\n";
