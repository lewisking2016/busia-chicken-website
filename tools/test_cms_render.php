<?php
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'superadmin';
$_SESSION['role'] = 'super_admin';
$_SESSION['first_name'] = 'Admin';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost:8000';
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/..';
$_SERVER['SCRIPT_NAME'] = '/Frontend/admin/website_cms.php';

ob_start();
include __DIR__ . '/../Frontend/admin/website_cms.php';
$html = ob_get_clean();

echo "Website CMS rendered: " . strlen($html) . " bytes\n";
if (strpos($html, 'Website CMS') !== false) {
    echo "SUCCESS: Title and controls rendered in HTML!\n";
} else {
    echo "WARNING: Could not find title in output\n";
}
