<?php
declare(strict_types=1);

require_once __DIR__ . '/../Backend/config/database.php';

try {
    $pdo = getDatabaseConnection();
    
    // Clear old testimonials (especially chicks/incubators)
    $pdo->exec("TRUNCATE TABLE testimonials");
    
    $eggReviews = [
        [
            'customer_name' => 'Daniel Ochieng',
            'customer_role' => 'Restaurant Owner, Busia Town (via Facebook)',
            'rating' => 5,
            'content' => 'Fresh eggs delivered to our restaurant every morning, without fail. The golden yolk richness and shell strength are unmatched in Busia County. Our breakfast customers always notice the difference!',
            'is_approved' => 1
        ],
        [
            'customer_name' => 'Mary Atieno',
            'customer_role' => 'Delight Pastries & Bakery, Kisumu (via Facebook)',
            'rating' => 5,
            'content' => 'We source over 100 trays of Grade-A eggs weekly from Busia Chicken for our bakeries in Kisumu. Shell strength and yolk freshness are exceptional. Zero breakage in transit and our cakes rise beautifully.',
            'is_approved' => 1
        ],
        [
            'customer_name' => 'Peter Wafula',
            'customer_role' => 'Supermarket Egg Buyer, Busia (via Facebook)',
            'rating' => 5,
            'content' => 'Busia Chicken delivers Grade-A eggs to our retail shelves directly from their Nasira facility. Consistent grading, hygienic 30-egg packaging, and our shoppers buy them out every single morning.',
            'is_approved' => 1
        ],
        [
            'customer_name' => 'Christine Nasimiyu',
            'customer_role' => 'Wholesale Egg Vendor, Tangakona Depot (via Facebook)',
            'rating' => 5,
            'content' => 'Picking up fresh egg crates at the Tangakona depot along the highway is fast and convenient. Genuine farm wholesale rates, spotless eggs, and they are literally harvested that very morning.',
            'is_approved' => 1
        ],
        [
            'customer_name' => 'Erick Barasa',
            'customer_role' => 'Continental Caterers & Events, Eldoret (via Facebook)',
            'rating' => 5,
            'content' => 'Ordered 50 trays of eggs for an executive catering event. Every single egg had thick albumen and rich orange yolks. Best egg supplier in Western Kenya by far.',
            'is_approved' => 1
        ],
        [
            'customer_name' => 'Grace Akello',
            'customer_role' => 'Sunrise Breakfast Cafe, Busia Border (via Facebook)',
            'rating' => 5,
            'content' => 'Consistent egg quality and size. Switched our breakfast orders to Busia Chicken 6 months ago and never looked back. The yolk flavor is rich, fresh, and perfectly balanced.',
            'is_approved' => 1
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO testimonials (customer_name, customer_role, rating, content, is_approved, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    
    foreach ($eggReviews as $r) {
        $stmt->execute([
            $r['customer_name'],
            $r['customer_role'],
            $r['rating'],
            $r['content'],
            $r['is_approved']
        ]);
        echo "Inserted Facebook egg review: " . $r['customer_name'] . PHP_EOL;
    }
    
    echo "SUCCESS: All testimonials are now 100% Facebook egg reviews!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
