<?php
require __DIR__ . '/../Backend/config/database.php';
$db = getDatabaseConnection();

// 1. Table for website hero sliders
$db->exec("CREATE TABLE IF NOT EXISTS website_sliders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slide_order INT DEFAULT 1,
    badge_text VARCHAR(255) DEFAULT 'Welcome to Busia Chicken Farm',
    title_main VARCHAR(255) DEFAULT 'Quality Poultry for',
    title_em VARCHAR(255) DEFAULT 'East Africa',
    subtitle TEXT,
    cta_primary_text VARCHAR(100) DEFAULT 'Explore Products',
    cta_primary_link VARCHAR(255) DEFAULT '/Frontend/pages/shop.php',
    cta_secondary_text VARCHAR(100) DEFAULT 'Our Story',
    cta_secondary_link VARCHAR(255) DEFAULT '/Frontend/pages/about.php',
    stat1_val VARCHAR(50) DEFAULT '100',
    stat1_suffix VARCHAR(20) DEFAULT 'k+',
    stat1_label VARCHAR(100) DEFAULT 'Chickens raised annually',
    stat2_val VARCHAR(50) DEFAULT '5000',
    stat2_suffix VARCHAR(20) DEFAULT '+',
    stat2_label VARCHAR(100) DEFAULT 'Happy customers',
    stat3_val VARCHAR(50) DEFAULT '13',
    stat3_suffix VARCHAR(20) DEFAULT '+',
    stat3_label VARCHAR(100) DEFAULT 'Years in business',
    image_url VARCHAR(255) DEFAULT '/Frontend/images/adbg.png',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 2. Insert default slides matching the boss's document if empty
$count = $db->query("SELECT COUNT(*) FROM website_sliders")->fetchColumn();
if ($count == 0) {
    $stmt = $db->prepare("INSERT INTO website_sliders 
        (slide_order, badge_text, title_main, title_em, subtitle, cta_primary_text, cta_primary_link, cta_secondary_text, cta_secondary_link, stat1_val, stat1_suffix, stat1_label, stat2_val, stat2_suffix, stat2_label, stat3_val, stat3_suffix, stat3_label, image_url, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Slide 1
    $stmt->execute([
        1,
        'Welcome to Busia Chicken Farm',
        'Quality Poultry for',
        'East Africa',
        'Farm-fresh eggs, quality layer feeds and organic manure — produced with care at our Busia farms and delivered with confidence.',
        'Explore Products',
        '/Frontend/pages/shop.php',
        'Our Story',
        '/Frontend/pages/about.php',
        '100',
        'k',
        'Chickens raised annually',
        '5000',
        '+',
        'Happy customers',
        '13',
        '+',
        'Years in business',
        '/Frontend/images/adbg.png',
        1
    ]);

    // Slide 2: The Egg People
    $stmt->execute([
        2,
        'The Egg People',
        'Farm-Fresh Eggs,',
        'Every Day',
        'State-of-the-art layer facilities producing the freshest, most nutritious eggs — harvested daily and delivered to your family or business.',
        'Order Fresh Eggs',
        '/Frontend/pages/shop.php?category=eggs',
        'See Our Products',
        '/Frontend/pages/products.php',
        '96',
        '%',
        'Eggs per bird per year',
        '100',
        '%',
        'Biosafety compliant',
        '100',
        '%',
        'Response time',
        '/Frontend/images/farm-fresh-eggs.jpg',
        1
    ]);

    // Slide 3: Expert Farm Management
    $stmt->execute([
        3,
        'Expert Farm Management',
        'Trusted by',
        'Thousands',
        'From consulting, egg & meat production to premium feeds — our dedicated team helps farmers grow with confidence.',
        'Explore Services',
        '/Frontend/pages/services.php',
        'Partner With Us',
        '/Frontend/pages/contact.php',
        '90',
        '%',
        'Hatch success rate',
        '30',
        '%',
        'Saved on feed costs',
        '4',
        ' Weeks',
        'To market weight',
        '/Frontend/images/download (2).png',
        1
    ]);
    echo "Default slides inserted successfully.\n";
} else {
    echo "Slides already exist ($count slides).\n";
}

// 3. Ensure site_settings has keys for locations and slogan
$defaults = [
    'farm_name' => 'Busia Chicken Limited',
    'farm_slogan' => 'The Chicken People',
    'farm_email' => 'info@busiachicken.com',
    'farm_phone' => '+254 727 585 599',
    'farm_whatsapp' => '+254727585599',
    'location_nasira' => 'Nasira AC Sub-location, Busibwabo, Busia County, Kenya (Nasira Farm)',
    'location_tangakona' => 'Tangakona Junction / Market, Busia-Kisumu Highway, Busia County, Kenya (Farm, Feeds & Sales Point)',
    'hours_nasira' => 'Mon - Sat: 7:00 AM - 6:00 PM | Sun: 8:00 AM - 1:00 PM',
    'hours_tangakona' => 'Mon - Sat: 8:00 AM - 6:30 PM | Sun: Closed',
    'google_maps_nasira' => 'https://www.google.com/maps/place/0%C2%B026\'06.0%22N+34%C2%B012\'07.6%22E/@0.4349969,34.1995236,17z/data=!3m1!4b1!4m4!3m3!8m2!3d0.4349969!4d34.2020985?hl=en&entry=ttu&g_ep=EgoyMDI2MTExNi4wIKXMDSoASAFQAw%3D%3D',
    'google_maps_tangakona' => 'https://www.google.com/maps?q=0.468734,34.2188207&z=17&hl=en',
    'announcement_bar' => '🐣 Farm-Fresh Grade-A Eggs, Layer Feeds & Organic Manure Available Daily in Nasira & Tangakona! Call +254 727 585 599',
    'show_announcement' => '1'
];

foreach ($defaults as $k => $v) {
    $exists = $db->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = ?");
    $exists->execute([$k]);
    if ($exists->fetchColumn() == 0) {
        $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)")->execute([$k, $v]);
    }
}
echo "Site settings initialized.\n";
