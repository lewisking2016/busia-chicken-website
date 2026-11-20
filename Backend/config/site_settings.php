<?php
/**
 * Site settings repository.
 *
 * `site_settings` is the key/value store behind every public page
 * (brand name, slogan, announcement bar, contact details, the two
 * Busia locations). It lives in its own module so that the frontend,
 * the admin CMS and the migration tooling all read and write it the
 * same way — previously each caller had its own inline SQL, so a
 * missing table produced a raw SQLSTATE error at save time.
 */
declare(strict_types=1);

if (!function_exists('tableExists')) {
    require_once __DIR__ . '/migrations.php';
}

/**
 * Create the table when it is missing. Cheap (one query) because the
 * caller only invokes it on a write path or an admin screen.
 */
function ensureSiteSettingsTable(PDO $pdo): bool
{
    if (tableExists($pdo, 'site_settings')) {
        // Older databases created this table with only key/value (settings.sql),
        // so the CMS write failed on the newer columns. Add them lazily.
        $missing = [];
        if (!columnExists($pdo, 'site_settings', 'setting_group')) {
            $missing[] = "ADD COLUMN setting_group VARCHAR(50) NOT NULL DEFAULT 'general'";
        }
        if (!columnExists($pdo, 'site_settings', 'description')) {
            $missing[] = 'ADD COLUMN description VARCHAR(255) NULL';
        }
        if ($missing) {
            try {
                $pdo->exec('ALTER TABLE site_settings ' . implode(', ', $missing));
            } catch (Throwable $e) {
                if (function_exists('busiaDbLog')) {
                    busiaDbLog('Could not extend site_settings: ' . $e->getMessage());
                }
            }
        }
        return true;
    }
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
            setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
            setting_value TEXT NULL,
            setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
            description VARCHAR(255) NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        return true;
    } catch (Throwable $e) {
        if (function_exists('busiaDbLog')) {
            busiaDbLog('Could not create site_settings: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Read one setting. Falls back to $default when the table or key is missing.
 */
function siteSettingGet(PDO $pdo, string $key, string $default = ''): string
{
    try {
        $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? (string)$value : $default;
    } catch (Throwable $e) {
        return $default;
    }
}

/**
 * Write one setting (insert or update).
 */
function siteSettingSet(PDO $pdo, string $key, string $value, string $group = 'general'): bool
{
    ensureSiteSettingsTable($pdo);
    try {
        $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value, setting_group)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        return $stmt->execute([$key, $value, $group]);
    } catch (Throwable $e) {
        if (function_exists('busiaDbLog')) {
            busiaDbLog('siteSettingSet(' . $key . ') failed: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * M-Pesa credentials as one merged map.
 *
 * Payment code used to read only the legacy `settings` table while the
 * admin screens wrote `site_settings`, so credentials were saved but never
 * picked up. Values in `site_settings` win (when non-empty) and legacy
 * rows stay as a fallback for databases that were never migrated.
 *
 * @return array<string,string>
 */
function busiaMpesaSettings(PDO $pdo): array
{
    $merged = [];

    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'mpesa\\_%'");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $merged[(string)$row['setting_key']] = (string)($row['setting_value'] ?? '');
        }
    } catch (Throwable $e) {
        // Legacy table may not exist on fresh installs.
    }

    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'mpesa\\_%'");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $value = (string)($row['setting_value'] ?? '');
            if ($value !== '') {
                $merged[(string)$row['setting_key']] = $value;
            }
        }
    } catch (Throwable $e) {
        // site_settings is created on the first write; ignore until then.
    }

    return $merged;
}

/**
 * Save a map of settings atomically. Returns the number of keys written.
 *
 * @param array<string,string> $values
 */
function siteSettingSaveAll(PDO $pdo, array $values, string $group = 'general'): int
{
    if (!$values) {
        return 0;
    }
    if (!ensureSiteSettingsTable($pdo)) {
        throw new RuntimeException('The site_settings table is missing and could not be created. Run tools/migrate.php to repair the database.');
    }

    $written = 0;
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value, setting_group)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($values as $key => $value) {
            $stmt->execute([(string)$key, (string)$value, $group]);
            $written++;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    return $written;
}
