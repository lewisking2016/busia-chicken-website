<?php
require __DIR__ . '/../Backend/config/database.php';
$db = getDatabaseConnection();

$reviews = [
    [
        'name' => 'Daniel Ochieng',
        'role' => 'Restaurant Owner, Busia Town',
        'rating' => 5,
        'content' => 'Fresh eggs delivered to our restaurant every morning, without fail. The golden yolk richness and shell strength are unmatched in Busia County. Our breakfast customers always notice the difference!',
        'approved' => 1
    ],
    [
        'name' => 'Mary Atieno',
        'role' => 'Delight Pastries & Bakery, Kisumu (via Facebook)',
        'rating' => 5,
        'content' => 'We source over 100 trays of Grade-A eggs weekly from Busia Chicken for our bakeries in Kisumu. Shell strength and yolk freshness are exceptional. Zero breakage in transit and our cakes rise beautifully.',
        'approved' => 1
    ],
    [
        'name' => 'Peter Wafula',
        'role' => 'Supermarket Egg Buyer, Busia (via Facebook)',
        'rating' => 5,
        'content' => 'Busia Chicken delivers Grade-A eggs to our retail shelves directly from their Nasira facility. Consistent grading, hygienic 30-egg packaging, and our shoppers buy them out every single morning.',
        'approved' => 1
    ],
    [
        'name' => 'Christine Nasimiyu',
        'role' => 'Wholesale Egg Vendor, Tangakona Depot (via Facebook)',
        'rating' => 5,
        'content' => 'Picking up fresh egg crates at the Tangakona depot along the highway is fast and convenient. Genuine farm wholesale rates, spotless eggs, and they are literally harvested that very morning.',
        'approved' => 1
    ],
    [
        'name' => 'Erick Barasa',
        'role' => 'Continental Caterers & Events, Eldoret (via Facebook)',
        'rating' => 5,
        'content' => 'Ordered 50 trays of eggs for an executive catering event. Every single egg had thick albumen and rich orange yolks. Best egg supplier in Western Kenya by far.',
        'approved' => 1
    ],
    [
        'name' => 'Grace Akello',
        'role' => 'Sunrise Breakfast Cafe, Busia Border (via Facebook)',
        'rating' => 5,
        'content' => 'Consistent egg quality and size. Switched our breakfast orders to Busia Chicken 6 months ago and never looked back. The yolk flavor is rich, fresh, and perfectly balanced.',
        'approved' => 1
    ]
];

foreach ($reviews as $rev) {
    $exists = $db->prepare("SELECT id FROM testimonials WHERE customer_name = ?");
    $exists->execute([$rev['name']]);
    if (!$exists->fetchColumn()) {
        $stmt = $db->prepare("INSERT INTO testimonials (customer_name, customer_role, rating, content, is_approved) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$rev['name'], $rev['role'], $rev['rating'], $rev['content'], $rev['approved']]);
        echo "Inserted testimonial from: {$rev['name']}\n";
    }
}
echo "Testimonials setup complete.\n";
