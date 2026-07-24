<?php
/**
 * Database Connection & Configuration
 * PDO-based database management for Busia Chicken Farm
 */
declare(strict_types=1);

// Database Configuration - Production Credentials
define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'mrhzdunf_busiachicken');
define('DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'mrhzdunf_busia_user');
define('DB_PASS', $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: 'busia_user');
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
        return $pdo;
    } catch (PDOException $e) {
        @error_log("Database connection failed: " . $e->getMessage());
        return null;
    } catch (Exception $e) {
        @error_log("Database connection exception: " . $e->getMessage());
        return null;
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
