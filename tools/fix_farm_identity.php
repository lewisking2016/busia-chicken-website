<?php
/**
 * Correct the farm identity shown on every public page.
 *
 * Footer/header render farm_name / farm_email / farm_phone / farm_address via
 * getSetting(), which reads the `site_settings` table (NOT `settings`).
 * These rows were seeded from the Wangari copy, so the public site was
 * rendering "© Wangari", info@wangari.farm and a Bungoma address.
 *
 * Values match the SITE_* constants in Frontend/includes/config.php.
 * Idempotent: re-running produces the same result.
 */
declare(strict_types=1);

$pdo = new PDO('mysql:host=localhost;dbname=busia_chicken_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// site_settings is the table getSetting() reads.
$table = 'site_settings';

$updates = [
    'farm_name'    => 'Busia Chicken Farm',
    'farm_email'   => 'info@busiachicken.com',
    'farm_phone'   => '+254 727 585 599',
    'farm_address' => 'Nasira AC Sub-location, Busibwabo Location, Busia County, Kenya',
];

$stmt = $pdo->prepare(
    "INSERT INTO {$table} (setting_key, setting_value) VALUES (:k, :v)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
);

foreach ($updates as $key => $value) {
    $stmt->execute([':k' => $key, ':v' => $value]);
    echo "set {$key} = {$value}\n";
}

// Keep the legacy `settings` table consistent so admin screens that read it
// (rather than site_settings) show the same farm identity.
$legacyStmt = $pdo->prepare(
    "INSERT INTO settings (setting_key, setting_value, setting_group, description)
     VALUES (:k, :v, 'farm', 'Public farm identity')
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
);
foreach ($updates as $key => $value) {
    $legacyStmt->execute([':k' => $key, ':v' => $value]);
}

echo "\n-- verify ({$table}) --\n";
foreach ($pdo->query("SELECT setting_key, setting_value FROM {$table}
                      WHERE setting_key LIKE 'farm_%' ORDER BY setting_key") as $r) {
    $flag = stripos((string)$r['setting_value'], 'wangari') !== false ? '  <-- STILL WANGARI' : '';
    echo "  {$r['setting_key']} = {$r['setting_value']}{$flag}\n";
}

echo "\n-- any remaining wangari references --\n";
$found = false;
foreach ($pdo->query("SELECT setting_key, setting_value FROM {$table}") as $r) {
    if (stripos((string)$r['setting_value'], 'wangari') !== false) {
        echo "  {$r['setting_key']} = {$r['setting_value']}\n";
        $found = true;
    }
}
if (!$found) {
    echo "  none\n";
}