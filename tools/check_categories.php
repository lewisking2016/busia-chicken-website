<?php
require __DIR__ . '/../Backend/config/database.php';
$db = getDatabaseConnection();
$cats = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
print_r($cats);
