<?php
require __DIR__ . '/../Backend/config/database.php';
$db = getDB();
$tables = ['settings', 'site_settings', 'testimonials', 'products'];
foreach ($tables as $t) {
    echo "=== TABLE: $t ===" . PHP_EOL;
    try {
        $cols = $db->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "  " . $c['Field'] . ' (' . $c['Type'] . ')' . PHP_EOL;
        }
        $count = $db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "  Total rows: $count" . PHP_EOL;
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . PHP_EOL;
    }
}
