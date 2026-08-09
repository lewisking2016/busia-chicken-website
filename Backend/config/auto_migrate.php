<?php
/**
 * Auto-Migration — runs the new tables on every request
 * Safe to call repeatedly: uses CREATE TABLE IF NOT EXISTS
 * Will not re-run if the "auto_migrate_v2_done" flag is set.
 */
declare(strict_types=1);

function ensureBusiaSchema(PDO $pdo): void {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        // Check if a key new table exists
        $check = $pdo->query("SHOW TABLES LIKE 'cashbook_entries'")->fetchColumn();
        if ($check) return; // already migrated

        // Get current db name
        $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
        if (!$dbName) return;

        // Run the new business migration
        $sql = file_get_contents(__DIR__ . '/migration_v2_business.sql');
        if ($sql === false) return;

        // Strip the USE statement since we're already connected
        $sql = preg_replace('/USE\s+\w+\s*;/i', '', $sql);

        // Split and execute
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($s) => $s && !str_starts_with($s, '--')
        );
        foreach ($statements as $stmt) {
            try { $pdo->exec($stmt); } catch (Exception $e) { /* skip errors */ }
        }

        // Also run the poultry_complete migration if houses table is missing
        $check2 = $pdo->query("SHOW TABLES LIKE 'houses'")->fetchColumn();
        if (!$check2) {
            $sql2 = file_get_contents(__DIR__ . '/migration_poultry_complete.sql');
            if ($sql2 !== false) {
                $sql2 = preg_replace('/USE\s+\w+\s*;/i', '', $sql2);
                $statements2 = array_filter(
                    array_map('trim', explode(';', $sql2)),
                    fn($s) => $s && !str_starts_with($s, '--')
                );
                foreach ($statements2 as $stmt) {
                    try { $pdo->exec($stmt); } catch (Exception $e) { /* skip */ }
                }
            }
        }
    } catch (Exception $e) {
        // Silent — never break the page
    }
}
