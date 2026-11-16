<?php
require __DIR__ . '/../Backend/config/database.php';
$db = getDatabaseConnection();
$cols = $db->query("DESCRIBE testimonials")->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
