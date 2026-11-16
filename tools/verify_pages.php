<?php
$pages = [
    'index.php' => 'Homepage',
    'Frontend/pages/about.php' => 'About Us',
    'Frontend/pages/services.php' => 'Our Services',
    'Frontend/pages/products.php' => 'Products',
    'Frontend/pages/contact.php' => 'Contact & Locations',
    'Frontend/pages/shop.php' => 'Shop',
    'Frontend/admin/login.php' => 'Admin Login',
    'Frontend/admin/website_cms.php' => 'Website CMS'
];

$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/..';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost:8000';

$allPassed = true;
foreach ($pages as $file => $label) {
    $fullPath = __DIR__ . '/../' . $file;
    if (!file_exists($fullPath)) {
        echo "FAIL: $label ($file) does not exist!\n";
        $allPassed = false;
        continue;
    }

    // Run php in a separate process to catch fatal errors
    $cmd = 'php -d display_errors=1 ' . escapeshellarg($fullPath);
    $output = shell_exec($cmd);
    if (strpos($output, 'Fatal error') !== false || strpos($output, 'Parse error') !== false) {
        echo "FAIL: $label ($file) threw an error:\n" . substr($output, 0, 300) . "\n";
        $allPassed = false;
    } else {
        echo "PASS: $label rendered successfully (" . strlen($output) . " bytes)\n";
    }
}

if ($allPassed) {
    echo "\nALL PAGES VERIFIED SUCCESSFULLY!\n";
} else {
    echo "\nSOME PAGES FAILED VERIFICATION!\n";
}
