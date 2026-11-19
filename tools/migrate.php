<?php
/**
 * Database migration & health tool.
 *
 * Usage:
 *   php tools/migrate.php            # apply pending migrations, show report
 *   php tools/migrate.php --force    # re-run every source, ignore the cache
 *   php tools/migrate.php --check    # report only, change nothing
 *
 * On cPanel without SSH you can also open it in a browser as a logged-in
 * administrator (maintenance_guard.php enforces that).
 */
declare(strict_types=1);

require __DIR__ . '/../Backend/config/maintenance_guard.php';
require_once __DIR__ . '/../Backend/config/database.php';
require_once __DIR__ . '/../Backend/config/migrations.php';
require_once __DIR__ . '/../Backend/config/cms.php';

$isCli = (PHP_SAPI === 'cli');
$args = $isCli ? array_slice($argv, 1) : [];
$force = in_array('--force', $args, true);
$checkOnly = in_array('--check', $args, true);

if (!$isCli) {
    header('Content-Type: text/plain; charset=UTF-8');
}

$pdo = getDatabaseConnection();
if (!$pdo) {
    echo "ERROR: cannot connect to the database.\n";
    echo 'Host: ' . DB_HOST . ' | Database: ' . DB_NAME . ' | User: ' . DB_USER . "\n";
    exit(1);
}

echo "Busia Chicken — database migration\n";
echo 'Database: ' . DB_NAME . ' | schema version: ' . BUSIA_SCHEMA_VERSION . "\n\n";

if ($checkOnly) {
    $report = busiaSchemaReport($pdo);
    echo 'Tables: ' . $report['table_count'] . "\n";
    echo $report['ok']
        ? "Schema OK — all required tables present.\n"
        : "Missing tables: " . implode(', ', $report['missing']) . "\n";
    exit($report['ok'] ? 0 : 1);
}

$report = busiaMigrate($pdo, [
    'force' => $force,
    'post' => static function (PDO $pdo): void {
        if (function_exists('reconcileLegacySchema')) {
            reconcileLegacySchema($pdo);
        }
        if (function_exists('seedMasterData')) {
            seedMasterData($pdo);
        }
        busiaSeedCmsContent($pdo);
        // Seed every editable website field declared in cms_defaults.php
        $cms = cms_boot($pdo);
        if ($cms['created'] > 0) {
            echo 'Seeded ' . $cms['created'] . " website content field(s).\n";
        }
    },
]);

echo 'Applied files: ' . ($report['applied'] ? implode(', ', $report['applied']) : '(none — already up to date)') . "\n";
echo 'Tables now: ' . $report['table_count'] . "\n";
echo 'Editable CMS fields: ' . array_sum(array_map(static fn($p) => count(cms_fields($p)), array_keys(cms_manifest()))) . "\n";
echo 'FAQ items: ' . (int)$pdo->query('SELECT COUNT(*) FROM cms_faq_items')->fetchColumn() . "\n";

if ($report['notes']) {
    echo "\nSkipped (already applied): " . count($report['notes']) . " statement(s)\n";
}

if ($report['ok']) {
    echo "\nSchema OK — all required tables present.\n";
    exit(0);
}

echo "\nSchema INCOMPLETE. Missing: " . implode(', ', $report['missing']) . "\n";
if ($report['errors']) {
    echo "\nErrors:\n";
    foreach ($report['errors'] as $error) {
        echo '  - ' . $error . "\n";
    }
    echo "\nFull log: logs/database.log\n";
}
exit(1);
