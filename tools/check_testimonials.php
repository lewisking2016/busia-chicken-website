<?php
require __DIR__ . '/../Backend/config/database.php';
$db = getDatabaseConnection();
$stmt = $db->query("SELECT * FROM testimonials");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total testimonials: " . count($rows) . "\n";
print_r($rows);
