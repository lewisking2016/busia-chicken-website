<?php
/**
 * Database Connection & Configuration
 * PDO-based database management for Busia Chicken Farm
 */
declare(strict_types=1);

// ---------------------------------------------------------------------
// Database Configuration
//
// Credentials are resolved in this order:
//   1. Backend/config/database.local.php  (gitignored — never commit this)
//   2. Environment variables DB_HOST / DB_NAME / DB_USER / DB_PASS
//   3. Local development defaults (root / empty password / busia_chicken_db)
//   4. Legacy production fallback (kept ONLY so the live site keeps
//      connecting during migration — see note below)
//
// Recommended production setup (cPanel): set the DB_* environment variables
// or drop a database.local.php next to this file containing, e.g.:
//     $DB_HOST = 'localhost';
//     $DB_NAME = 'your_cpanel_db_name';
//     $DB_USER = 'your_cpanel_db_user';
//     $DB_PASS = 'your_cpanel_db_password';
//
// SECURITY NOTE: the production fallback below is legacy and must be
// removed after rotating the cPanel database password. Rotating the
// password invalidates the old credentials everywhere (including git
// history), then move the new credentials into database.local.php or
// DB_* env vars and delete the fallback block.
// ---------------------------------------------------------------------
$localConfigFile = __DIR__ . '/database.local.php';
if (is_file($localConfigFile)) {
    @include $localConfigFile;
}

$dbEnv = function (string $key): ?string {
    $value = $_ENV[$key] ?? getenv($key);
    return ($value === false || $value === '') ? null : (string)$value;
};

// Local-environment detection (CLI, localhost host, or local docroot).
$isCli = (php_sapi_name() === 'cli');
$isLocalhost = $isCli
    || in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost:8000', 'localhost', '127.0.0.1'], true)
    || ($_ENV['DB_HOST'] ?? getenv('DB_HOST')) === 'localhost'
    || (!empty($_SERVER['DOCUMENT_ROOT'])
        && (str_contains($_SERVER['DOCUMENT_ROOT'], 'Users') || str_contains($_SERVER['DOCUMENT_ROOT'], 'Desktop')));

// Per-environment defaults. The production values are the legacy fallback
// that keeps the LIVE site (new.decapoli.co.ke) connected — rotate the
// password and move them out of this file when possible (see note above).
$defaults = $isLocalhost
    ? ['localhost', 'busia_chicken_db', 'root', '']
    : ['localhost', 'mrhzdunf_busiachicken', 'mrhzdunf_busia_user', 'busia_user'];

define('DB_HOST', $DB_HOST ?? $dbEnv('DB_HOST') ?? $defaults[0]);
define('DB_NAME', $DB_NAME ?? $dbEnv('DB_NAME') ?? $defaults[1]);
define('DB_USER', $DB_USER ?? $dbEnv('DB_USER') ?? $defaults[2]);
define('DB_PASS', $DB_PASS ?? $dbEnv('DB_PASS') ?? $defaults[3]);
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4');

// PDO Options
$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
];

// Connection String (DSN)
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// Auto-migration helper is safe to include repeatedly
@require_once __DIR__ . '/auto_migrate.php';

// Global PDO instance
$pdo = null;

/**
 * Get Database Connection
 */
function getDatabaseConnection(): ?PDO {
    global $pdo, $pdoOptions;
    
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        if (function_exists('ensureBusiaSchema')) {
            try {
                ensureBusiaSchema($pdo);
            } catch (Exception $e) {
                @error_log('Auto schema ensure failed: ' . $e->getMessage());
            }
        }

        return $pdo;
    } catch (PDOException $e) {
        @error_log("Database connection failed: " . $e->getMessage());
        return null;
    } catch (Exception $e) {
        @error_log("Database connection exception: " . $e->getMessage());
        return null;
    }
}

/**
 * Check whether a table exists in the current database.
 */
function tableExists(PDO $pdo, string $table): bool {
    try {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        @error_log("tableExists failed for {$table}: " . $e->getMessage());
        return false;
    }
}

/**
 * Check whether a column exists on a table in the current database.
 */
function columnExists(PDO $pdo, string $table, string $column): bool {
    try {
        $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1");
        $stmt->execute([$table, $column]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        @error_log("columnExists failed for {$table}.{$column}: " . $e->getMessage());
        return false;
    }
}

// Try to initialize connection - NEVER throw errors, always return null on failure
try {
    $pdo = getDatabaseConnection();
} catch (PDOException $e) {
    // Log error but don't die - let frontend handle it gracefully
    @error_log("Initial database connection failed: " . $e->getMessage());
    $pdo = null;
} catch (Exception $e) {
    @error_log("Database connection exception: " . $e->getMessage());
    $pdo = null;
}

/**
 * Database Helper Functions
 */

/**
 * Escape and sanitize string output
 */
function escape(string $raw): string {
    return htmlspecialchars($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Fetch single row
 */
function fetchOne(PDO $pdo, string $query, array $params = []): ?array {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return null;
    }
}

/**
 * Fetch all rows
 */
function fetchAll(PDO $pdo, string $query, array $params = []): array {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return [];
    }
}

/**
 * Execute insert/update/delete query
 */
function execute(PDO $pdo, string $query, array $params = []): bool {
    try {
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get last inserted ID
 */
function lastInsertId(PDO $pdo): string {
    return $pdo->lastInsertId();
}

/**
 * Get row count from last query
 */
function rowCount(PDO $pdo, string $query, array $params = []): int {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Database Health Check
 */
function checkDatabaseHealth(PDO $pdo): bool {
    try {
        $result = $pdo->query("SELECT 1");
        return $result !== false;
    } catch (PDOException $e) {
        error_log("Database health check failed: " . $e->getMessage());
        return false;
    }
}

?>
