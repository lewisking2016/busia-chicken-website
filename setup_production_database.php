<?php
/**
 * Production Database Setup Script
 * Run this ONCE on the live server to create tables and insert data
 */
declare(strict_types=1);

// Production Database Configuration
const DB_HOST = 'localhost';
const DB_NAME = 'mrhzdunf_busiachicken';
const DB_USER = 'mrhzdunf_busia_user';
const DB_PASS = 'busia_user';
const DB_CHARSET = 'utf8mb4';

echo "<!DOCTYPE html><html><head><title>Database Setup</title><style>body{font-family:system-ui;padding:40px;max-width:800px;margin:0 auto;background:#f5f5f5;}h1{color:#2c3e50;}pre{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}code{display:block;margin:10px 0;padding:10px;background:#f8f9fa;border-left:3px solid #28a745;}code.error{border-left-color:#dc3545;background:#fee;}</style></head><body>";
echo "<h1>🐔 Busia Chicken Farm - Production Database Setup</h1>";
echo "<pre>";

try {
    // Connect to database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "<code>✓ Database connection successful</code>";

    // Read schema file
    $schemaFile = __DIR__ . '/Backend/config/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    $schema = file_get_contents($schemaFile);
    
    // Replace database name in schema
    $schema = str_replace('busia_chicken_db', DB_NAME, $schema);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    $tableCount = 0;
    foreach ($statements as $statement) {
        if (!empty($statement) && strpos($statement, 'CREATE') !== false) {
            $pdo->exec($statement);
            $tableCount++;
            // Extract table name for display
            preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches);
            $tableName = $matches[1] ?? 'unknown';
            echo "<code>✓ Created table: {$tableName}</code>";
        }
    }

    echo "<code>✓ All {$tableCount} tables created successfully</code>";

    // Insert categories
    $categories = [
        ['name' => 'Broilers', 'slug' => 'broilers', 'category_type' => 'chicken', 'description' => 'Fast-growing broiler chickens for meat production'],
        ['name' => 'Layers', 'slug' => 'layers', 'category_type' => 'chicken', 'description' => 'High-productivity layer chickens for egg production'],
        ['name' => 'Day-Old Chicks', 'slug' => 'day-old-chicks', 'category_type' => 'chicken', 'description' => 'Vaccinated day-old chicks ready for rearing'],
        ['name' => 'Feeds', 'slug' => 'feeds', 'category_type' => 'feed', 'description' => 'Specialized animal feeds for optimal poultry nutrition'],
    ];

    $catStmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug, category_type, description) VALUES (?, ?, ?, ?)");
    $catInserted = 0;
    foreach ($categories as $cat) {
        if ($catStmt->execute([$cat['name'], $cat['slug'], $cat['category_type'], $cat['description']])) {
            $catInserted++;
        }
    }
    echo "<code>✓ Inserted {$catInserted} categories</code>";

    // Get category IDs
    $broilersCat = $pdo->query("SELECT id FROM categories WHERE slug = 'broilers' LIMIT 1")->fetch();
    $layersCat = $pdo->query("SELECT id FROM categories WHERE slug = 'layers' LIMIT 1")->fetch();
    $chicksCat = $pdo->query("SELECT id FROM categories WHERE slug = 'day-old-chicks' LIMIT 1")->fetch();
    $feedCat = $pdo->query("SELECT id FROM categories WHERE slug = 'feeds' LIMIT 1")->fetch();

    $chickenCatId = $broilersCat['id'] ?? 1;
    $layerCatId = $layersCat['id'] ?? 2;
    $chickCatId = $chicksCat['id'] ?? 3;
    $feedCatId = $feedCat['id'] ?? 4;

    // Insert products
    $products = [
        // Broilers
        [$chickenCatId, 'Ross 308 Broilers', 'ross-308-broilers', 'live_chicken', 450, 50, 'Premium fast-growing broiler breed. Excellent feed efficiency and meat quality.', 1, 1],
        [$chickenCatId, 'Cobb 500 Broilers', 'cobb-500-broilers', 'live_chicken', 480, 40, 'High-performance broilers with excellent feed conversion.', 1, 1],
        [$chickenCatId, 'Hubbard Broilers', 'hubbard-broilers', 'live_chicken', 420, 60, 'Reliable broiler breed with consistent meat quality.', 1, 0],
        
        // Layers
        [$layerCatId, 'ISA Brown Layers', 'isa-brown-layers', 'live_chicken', 350, 45, 'Premium brown egg layer producing 300+ eggs/year.', 1, 1],
        [$layerCatId, 'Fresh Farm Eggs (Trays)', 'fresh-farm-eggs', 'eggs', 420, 100, 'Premium quality eggs from our layer flock. 30-egg trays.', 1, 1],
        [$layerCatId, 'Lohmann Layers', 'lohmann-layers', 'live_chicken', 340, 55, 'White egg layers with exceptional livability.', 1, 0],
        
        // Chicks
        [$chickCatId, 'Day-Old Broiler Chicks', 'day-old-broiler-chicks', 'chicks', 80, 1000, 'Vaccinated broiler chicks. 95%+ hatch rate.', 1, 1],
        [$chickCatId, 'Day-Old Layer Chicks', 'day-old-layer-chicks', 'chicks', 70, 800, 'Premium layer chicks vaccinated and ready to grow.', 1, 1],
        [$chickCatId, 'Mixed Day-Old Chicks', 'mixed-day-old-chicks', 'chicks', 75, 500, 'Combination of broiler and layer chicks.', 1, 0],
        
        // Feeds
        [$feedCatId, 'Starter Feed (0-4 weeks)', 'starter-feed', 'feed', 3200, 100, 'High-protein formula for day-old chicks. 24% crude protein with probiotics.', 1, 1],
        [$feedCatId, 'Grower Feed (4-8 weeks)', 'grower-feed', 'feed', 2800, 120, 'Balanced formula for growing chicks. 20% crude protein.', 1, 1],
        [$feedCatId, 'Layer Mash (16 weeks+)', 'layer-mash', 'feed', 2500, 150, 'Premium feed for laying hens. 18% crude protein with calcium.', 1, 1],
        [$feedCatId, 'Broiler Finisher (6-8 weeks)', 'broiler-finisher', 'feed', 2900, 110, 'Final stage feed for broilers. High energy formula.', 1, 0],
        [$feedCatId, 'Busia Premium Mix', 'busia-premium-mix', 'feed', 3100, 200, 'Our signature blend. Multi-purpose feed suitable for all poultry types.', 1, 1],
        [$feedCatId, 'Vitamin & Mineral Supplements', 'vitamin-mineral-supplements', 'feed', 1200, 80, 'Complete vitamin complex and mineral pack for all poultry.', 1, 0],
    ];

    $prodStmt = $pdo->prepare("INSERT IGNORE INTO products (category_id, name, slug, product_type, price, stock_quantity, description, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $prodInserted = 0;
    foreach ($products as $prod) {
        if ($prodStmt->execute($prod)) {
            $prodInserted++;
        }
    }
    echo "<code>✓ Inserted {$prodInserted} products</code>";

    // Insert Admin User
    $admin_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, email, password_hash, role, first_name, last_name) VALUES ('admin', 'admin@busiachicken.com', '$admin_password_hash', 'super_admin', 'Admin', 'User')");
    echo "<code>✓ Inserted admin user (admin / admin123)</code>";

    // Insert Demo User
    $password_hash = password_hash('demo123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, email, password_hash, role, first_name, last_name) VALUES ('demo', 'demo@example.com', '$password_hash', 'customer', 'Demo', 'User')");
    echo "<code>✓ Inserted demo user (demo / demo123)</code>";

    echo "<code style='border-left-color:#28a745;background:#d4edda;'>✓ DATABASE SETUP COMPLETE!</code>";
    echo "<code>You can now visit: <a href='/' style='color:#007bff;'>https://new.kindcommoditiesltd.com</a></code>";
    echo "<code style='background:#fff3cd;border-left-color:#ffc107;'>⚠ IMPORTANT: Delete this file (setup_production_database.php) after successful setup for security!</code>";

} catch (PDOException $e) {
    echo "<code class='error'>✗ Database Error: " . htmlspecialchars($e->getMessage()) . "</code>";
} catch (Exception $e) {
    echo "<code class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</code>";
}

echo "</pre></body></html>";
?>
