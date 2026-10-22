<?php
/**
 * diagnose_migration.php — run the poultry migration statement-by-statement
 * against a scratch DB and print the real error for each failure.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

$admin = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$scratch = 'busia_schema_probe';
$admin->exec("DROP DATABASE IF EXISTS `{$scratch}`");
$admin->exec("CREATE DATABASE `{$scratch}`");
$pdo = new PDO("mysql:host={$host};dbname={$scratch};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

require_once $root . '/Backend/config/auto_migrate.php';

// Base schema first
$base = (string)file_get_contents($root . '/Backend/config/schema.sql');
$base = preg_replace('/USE\s+`?\w+`?\s*;/i', '', $base);
$base = preg_replace('/DROP\s+TABLE[^;]*;/i', '', $base);
foreach (array_filter(array_map('trim', explode(';', $base))) as $stmt) {
    if ($stmt === '' || str_starts_with($stmt, '--')) continue;
    try { $pdo->exec($stmt); } catch (Exception $e) {}
}

foreach (['migration_poultry_complete.sql', 'migration_v2_business.sql'] as $file) {
    echo "\n########## $file ##########\n";
    $sql = (string)file_get_contents($root . '/Backend/config/' . $file);
    $ok = 0; $fail = 0;
    foreach (splitMigrationSql($sql) as $i => $stmt) {
        $head = substr(preg_replace('/\s+/', ' ', $stmt), 0, 78);
        try {
            $pdo->exec($stmt);
            $ok++;
        } catch (Exception $e) {
            $fail++;
            echo "[FAIL #{$i}] {$head}\n        -> " . $e->getMessage() . "\n";
        }
    }
    echo "-- {$file}: {$ok} ok, {$fail} failed\n";
}

$admin->exec("DROP DATABASE IF EXISTS `{$scratch}`");
echo "\n(scratch dropped)\n";