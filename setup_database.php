<?php
/**
 * Database Setup Script
 * Creates all tables and inserts sample data
 */
declare(strict_types=1);

// Database Configuration
const DB_HOST = 'localhost';
const DB_NAME = 'busia_chicken_db';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

try {
    // Connect to database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✓ Database connection successful\n\n";

    // Read and execute schema
    $schemaFile = __DIR__ . '/Backend/config/schema.sql';
    if (!file_exists($schemaFile)) {
        die("Schema file not found: $schemaFile\n");
    }

    $schema = file_get_contents($schemaFile);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }

    echo "\n✓ All tables created successfully!\n\n";

    // Insert sample categories
    $categories = [
        ['name' => 'Broilers', 'slug' => 'broilers', 'category_type' => 'chicken', 'description' => 'Fast-growing broiler chickens for meat production'],
        ['name' => 'Layers', 'slug' => 'layers', 'category_type' => 'chicken', 'description' => 'High-productivity layer chickens for egg production'],
        ['name' => 'Day-Old Chicks', 'slug' => 'day-old-chicks', 'category_type' => 'chicken', 'description' => 'Vaccinated day-old chicks ready for rearing'],
        ['name' => 'Feeds', 'slug' => 'feeds', 'category_type' => 'feed', 'description' => 'Specialized animal feeds for optimal poultry nutrition'],
    ];

    $catStmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug, category_type, description) VALUES (?, ?, ?, ?)");
    foreach ($categories as $cat) {
        $catStmt->execute([$cat['name'], $cat['slug'], $cat['category_type'], $cat['description']]);
        echo "✓ Inserted category: {$cat['name']}\n";
    }

    echo "\n✓ Categories inserted!\n\n";

    // Get category IDs
    $chickenCatId = $pdo->query("SELECT id FROM categories WHERE slug = 'broilers' LIMIT 1")->fetch()['id'];
    $feedCatId = $pdo->query("SELECT id FROM categories WHERE slug = 'feeds' LIMIT 1")->fetch()['id'];

    // Insert sample products
    $products = [
        // Broilers
        ['category_id' => $chickenCatId, 'name' => 'Ross 308 Broilers', 'slug' => 'ross-308-broilers', 'product_type' => 'live_chicken', 'price' => 450, 'stock_quantity' => 50, 'description' => 'Premium fast-growing broiler breed. Excellent feed efficiency and meat quality.'],
        ['category_id' => $chickenCatId, 'name' => 'Cobb 500 Broilers', 'slug' => 'cobb-500-broilers', 'product_type' => 'live_chicken', 'price' => 480, 'stock_quantity' => 40, 'description' => 'High-performance broilers with excellent feed conversion.'],
        ['category_id' => $chickenCatId, 'name' => 'Hubbard Broilers', 'slug' => 'hubbard-broilers', 'product_type' => 'live_chicken', 'price' => 420, 'stock_quantity' => 60, 'description' => 'Reliable broiler breed with consistent meat quality.'],
        
        // Layers
        ['category_id' => $chickenCatId, 'name' => 'ISA Brown Layers', 'slug' => 'isa-brown-layers', 'product_type' => 'live_chicken', 'price' => 350, 'stock_quantity' => 45, 'description' => 'Premium brown egg layer producing 300+ eggs/year.'],
        ['category_id' => $chickenCatId, 'name' => 'Fresh Farm Eggs (Trays)', 'slug' => 'fresh-farm-eggs', 'product_type' => 'eggs', 'price' => 420, 'stock_quantity' => 100, 'description' => 'Premium quality eggs from our layer flock. 30-egg trays.'],
        ['category_id' => $chickenCatId, 'name' => 'Lohmann Layers', 'slug' => 'lohmann-layers', 'product_type' => 'live_chicken', 'price' => 340, 'stock_quantity' => 55, 'description' => 'White egg layers with exceptional livability.'],
        
        // Chicks
        ['category_id' => $chickenCatId, 'name' => 'Day-Old Broiler Chicks', 'slug' => 'day-old-broiler-chicks', 'product_type' => 'chicks', 'price' => 80, 'stock_quantity' => 1000, 'description' => 'Vaccinated broiler chicks. 95%+ hatch rate.'],
        ['category_id' => $chickenCatId, 'name' => 'Day-Old Layer Chicks', 'slug' => 'day-old-layer-chicks', 'product_type' => 'chicks', 'price' => 70, 'stock_quantity' => 800, 'description' => 'Premium layer chicks vaccinated and ready to grow.'],
        ['category_id' => $chickenCatId, 'name' => 'Mixed Day-Old Chicks', 'slug' => 'mixed-day-old-chicks', 'product_type' => 'chicks', 'price' => 75, 'stock_quantity' => 500, 'description' => 'Combination of broiler and layer chicks.'],
        
        // Feeds
        ['category_id' => $feedCatId, 'name' => 'Starter Feed (0-4 weeks)', 'slug' => 'starter-feed', 'product_type' => 'feed', 'price' => 3200, 'stock_quantity' => 100, 'description' => 'High-protein formula for day-old chicks. 24% crude protein with probiotics.'],
        ['category_id' => $feedCatId, 'name' => 'Grower Feed (4-8 weeks)', 'slug' => 'grower-feed', 'product_type' => 'feed', 'price' => 2800, 'stock_quantity' => 120, 'description' => 'Balanced formula for growing chicks. 20% crude protein.'],
        ['category_id' => $feedCatId, 'name' => 'Layer Mash (16 weeks+)', 'slug' => 'layer-mash', 'product_type' => 'feed', 'price' => 2500, 'stock_quantity' => 150, 'description' => 'Premium feed for laying hens. 18% crude protein with calcium.'],
        ['category_id' => $feedCatId, 'name' => 'Broiler Finisher (6-8 weeks)', 'slug' => 'broiler-finisher', 'product_type' => 'feed', 'price' => 2900, 'stock_quantity' => 110, 'description' => 'Final stage feed for broilers. High energy formula.'],
        ['category_id' => $feedCatId, 'name' => 'Busia Premium Mix', 'slug' => 'busia-premium-mix', 'product_type' => 'feed', 'price' => 3100, 'stock_quantity' => 200, 'description' => 'Our signature blend. Multi-purpose feed suitable for all poultry types.'],
        ['category_id' => $feedCatId, 'name' => 'Vitamin & Mineral Supplements', 'slug' => 'vitamin-mineral-supplements', 'product_type' => 'feed', 'price' => 1200, 'stock_quantity' => 80, 'description' => 'Complete vitamin complex and mineral pack for all poultry.'],
    ];

    $prodStmt = $pdo->prepare("INSERT IGNORE INTO products (category_id, name, slug, product_type, price, stock_quantity, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $prod) {
        $prodStmt->execute([$prod['category_id'], $prod['name'], $prod['slug'], $prod['product_type'], $prod['price'], $prod['stock_quantity'], $prod['description']]);
        echo "✓ Inserted product: {$prod['name']}\n";
    }

    // Insert Demo User
    $password_hash = password_hash('demo123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, email, password_hash, role, first_name, last_name) VALUES ('demo', 'demo@example.com', '$password_hash', 'customer', 'Demo', 'User')");
    echo "✓ Inserted demo user (demo / demo123)\n";

    // Insert Admin User
    $admin_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, email, password_hash, role, first_name, last_name) VALUES ('admin', 'admin@example.com', '$admin_password_hash', 'super_admin', 'Admin', 'User')");
    echo "✓ Inserted admin user (admin / admin123)\n";

    echo "\n✓ Products inserted successfully!\n";
    echo "\n========================================\n";
    echo "✓ DATABASE SETUP COMPLETE!\n";
    echo "========================================\n";
    echo "\nYou can now access the website at:\n";
    echo "http://localhost:8000/Frontend/\n\n";

} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
