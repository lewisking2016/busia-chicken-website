<?php
require __DIR__ . '/../Backend/config/database.php';
$db = getDatabaseConnection();
$db->exec("DELETE FROM products WHERE name LIKE '%Wangari%'");
echo "Cleaned up old products.\n";
