<?php
declare(strict_types=1);

ob_start();
$_SERVER['REQUEST_URI'] = '/';
require_once __DIR__ . '/../Frontend/index.php';
$html = ob_get_clean();

$checks = [
    'James Muriithi' => false,
    'Alice Nekesa' => false,
    'Daniel Ochieng' => true,
    'Mary Atieno' => true,
    'Peter Wafula' => true,
    'Christine Nasimiyu' => true,
    'Erick Barasa' => true,
    'Grace Akello' => true,
    'site-header' => true,
    'has-announcement' => true,
    'VERIFIED FACEBOOK REVIEWS • EGGS ONLY' => true,
];

foreach ($checks as $term => $shouldExist) {
    $exists = (strpos($html, $term) !== false);
    if ($exists === $shouldExist) {
        echo "PASS: '$term' " . ($shouldExist ? "found" : "NOT found") . PHP_EOL;
    } else {
        echo "FAIL: '$term' expected " . ($shouldExist ? "to exist" : "NOT to exist") . " but " . ($exists ? "exists" : "missing") . PHP_EOL;
    }
}
