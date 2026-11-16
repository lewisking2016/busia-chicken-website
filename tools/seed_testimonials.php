<?php
require __DIR__ . '/../Backend/config/database.php';
$db = getDatabaseConnection();

$reviews = [
    [
        'name' => 'Daniel Ochieng',
        'role' => 'Restaurant Owner, Busia Town',
        'rating' => 5,
        'content' => 'Fresh eggs delivered to our restaurant every morning, without fail. The egg quality and rich golden yolks are unmatched in the county. Our breakfast customers love them.',
        'approved' => 1
    ],
    [
        'name' => 'Mary Atieno',
        'role' => 'Executive Chef & Bakery Owner, Kisumu',
        'rating' => 5,
        'content' => 'We source 120+ trays of Busia Farm eggs weekly for our bakeries in Kisumu. Shell strength and yolk consistency are exceptional. Zero spoilage and always farm-fresh.',
        'approved' => 1
    ],
    [
        'name' => 'Alice Nekesa',
        'role' => 'Hatchery Owner & Egg Distributor, Bungoma',
        'rating' => 5,
        'content' => 'The incubator rental service and fertile egg supply changed our business. We hatch over 2,000 chicks monthly with an amazing success rate, and their table eggs sell out within hours.',
        'approved' => 1
    ],
    [
        'name' => 'James Muriithi',
        'role' => 'Broiler & Layer Farmer, Eldoret',
        'rating' => 5,
        'content' => "I've bought day-old chicks and layers from Busia for three seasons now. Mortality is almost zero, their feeds are high quality, and the support team actually answers the phone.",
        'approved' => 1
    ],
    [
        'name' => 'Peter Wafula',
        'role' => 'Supermarket Wholesale Buyer, Busia',
        'rating' => 5,
        'content' => 'Busia Chicken Farm delivers Grade-A eggs to our retail shelves directly from their Nasira facility. Consistent grading, hygienic packaging in 30-egg trays, and prompt customer service.',
        'approved' => 1
    ],
    [
        'name' => 'Christine Nasimiyu',
        'role' => 'Wholesale Egg Vendor, Tangakona Depot',
        'rating' => 5,
        'content' => 'Picking up fresh crates at the Tangakona depot is seamless. Genuine farm prices, friendly staff, and the eggs are literally harvested that very morning.',
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
