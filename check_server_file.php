<?php
/**
 * Check Server File Version
 */

$file = __DIR__ . '/Frontend/admin/bulk_import_export.php';
$content = file_get_contents($file);

// Check for the fixed code
$has_phone_number = strpos($content, 'phone_number') !== false;
$has_old_phone = strpos($content, "phone, role") !== false;
$has_financial_records = strpos($content, 'financial_records') !== false;
$has_old_expenses = strpos($content, "FROM expenses") !== false;
$has_initial_count = strpos($content, 'initial_count') !== false;
$has_old_total_birds = strpos($content, 'total_birds') !== false;

echo "<!DOCTYPE html><html><head><title>Server File Check</title>";
echo "<style>body{font-family:sans-serif;padding:40px;background:#f5f5f5;max-width:800px;margin:0 auto;}";
echo ".ok{color:#16a34a;font-weight:bold;} .fail{color:#dc2626;font-weight:bold;} .box{background:#fff;padding:20px;border-radius:8px;margin:10px 0;border:1px solid #e5e7eb;}</style></head><body>";

echo "<h1>🔍 Server File Version Check</h1>";
echo "<p>Checking: <code>$file</code></p>";

echo "<div class='box'>";
echo "<h2>Users Table (phone vs phone_number)</h2>";
echo "<p>Has phone_number (CORRECT): " . ($has_phone_number ? "<span class='ok'>✓ YES</span>" : "<span class='fail'>✗ NO</span>") . "</p>";
echo "<p>Has old 'phone' (WRONG): " . ($has_old_phone ? "<span class='fail'>✓ YES - NEEDS UPDATE</span>" : "<span class='ok'>✗ NO</span>") . "</p>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>Expenses Table (financial_records vs expenses)</h2>";
echo "<p>Has financial_records (CORRECT): " . ($has_financial_records ? "<span class='ok'>✓ YES</span>" : "<span class='fail'>✗ NO</span>") . "</p>";
echo "<p>Has old 'expenses' (WRONG): " . ($has_old_expenses ? "<span class='fail'>✓ YES - NEEDS UPDATE</span>" : "<span class='ok'>✗ NO</span>") . "</p>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>Flocks Table (initial_count vs total_birds)</h2>";
echo "<p>Has initial_count (CORRECT): " . ($has_initial_count ? "<span class='ok'>✓ YES</span>" : "<span class='fail'>✗ NO</span>") . "</p>";
echo "<p>Has old 'total_birds' (WRONG): " . ($has_old_total_birds ? "<span class='fail'>✓ YES - NEEDS UPDATE</span>" : "<span class='ok'>✗ NO</span>") . "</p>";
echo "</div>";

if ($has_phone_number && $has_financial_records && $has_initial_count && !$has_old_phone && !$has_old_expenses && !$has_old_total_birds) {
    echo "<div class='box' style='background:#dcfce7;border-color:#16a34a;'>";
    echo "<h2 class='ok'>✓ FILE IS UP TO DATE!</h2>";
    echo "<p>The server has the latest version. Exports should work now.</p>";
    echo "</div>";
} else {
    echo "<div class='box' style='background:#fee2e2;border-color:#dc2626;'>";
    echo "<h2 class='fail'>✗ FILE IS OUTDATED!</h2>";
    echo "<p>The server still has the old version. Git pull did not update the file.</p>";
    echo "<p><strong>Solution:</strong> Manually upload the file via cPanel File Manager or use FTP.</p>";
    echo "</div>";
}

echo "<p><a href='/Frontend/admin/bulk_import_export.php'>Test Bulk Import/Export</a> | <a href='/'>Homepage</a></p>";

echo "</body></html>";
?>
