<?php
/**
 * Frontend Configuration & Session Setup
 * Global configuration constants and database connection initialization
 */
declare(strict_types=1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    $temp_dir = sys_get_temp_dir();
    if (is_writable($temp_dir)) {
        session_save_path($temp_dir);
    }
    // Secure Session Configuration parameters
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// Detect BASE_URL automatically
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
$docRoot   = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);

if (str_contains($scriptDir, '/Frontend') || str_contains($scriptDir, '/Frontend/pages')) {
    define('BASE_URL', '/Frontend/');
} else {
    define('BASE_URL', '/Frontend/');
}

define('ASSETS_URL', BASE_URL . 'assets/');
define('API_URL', '/Backend/api/');

// Environment Detection
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', APP_ENV === 'development');

// Site Information
define('SITE_NAME', 'Busia Chicken Limited');
define('SITE_TAGLINE', 'The Chicken People');
define('SITE_SLOGAN', 'The Chicken People');
define('SITE_DESCRIPTION', 'Leading poultry supplier in East Africa. Quality farm-fresh eggs, layer feeds and organic fertiliser since 2015.');
define('SITE_EMAIL', 'info@busiachicken.com');
define('SITE_PHONE', '+254 727 585 599');
define('SITE_WHATSAPP', '+254727585599');
define('SITE_ADDRESS_NASIRA', 'Nasira AC Sub-location, Busibwabo, Busia County, Kenya (Nasira Farm)');
define('SITE_ADDRESS_TANGAKONA', 'Tangakona Junction / Market, Busia-Kisumu Highway, Busia County, Kenya (Farm, Feeds & Sales Point)');
define('SITE_ADDRESS', 'Nasira AC Sub-location & Tangakona, Busia County, Kenya');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Currency
define('CURRENCY', 'KES');
define('CURRENCY_SYMBOL', 'KES');

// Payment Methods (No M-Pesa)
define('PAYMENT_METHODS', [
    'bank_transfer' => 'Bank Transfer',
    'cash_on_delivery' => 'Cash on Delivery'
]);

// Order Status
define('ORDER_STATUS', [
    'pending' => 'Pending',
    'paid' => 'Paid',
    'picking' => 'Picking',
    'packing' => 'Packing',
    'production' => 'In Production',
    'dispatch' => 'Dispatch',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
]);

// Delivery Zones
define('DELIVERY_ZONES', [
    'busia' => ['name' => 'Busia County', 'cost' => 0],
    'kakamega' => ['name' => 'Kakamega County', 'cost' => 500],
    'kisumu' => ['name' => 'Kisumu County', 'cost' => 1000],
    'kisii' => ['name' => 'Kisii County', 'cost' => 1200],
]);

// Minimum Order Value & Free Delivery
define('MIN_ORDER_VALUE', 2000);
define('FREE_DELIVERY_THRESHOLD', 5000);

// Product Categories
define('PRODUCT_CATEGORIES', [
    'chicken' => 'Chicken Products',
    'feeds' => 'Animal Feeds'
]);

// Product Types
define('PRODUCT_TYPES', [
    'feed' => 'Layer Feed',
    'eggs' => 'Fresh Eggs',
    'organic' => 'Organic Fertiliser',
    'live_chicken' => 'Live Chicken'
]);

// Include Backend Configuration Files
try {
    $backendPath = dirname(__DIR__, 2) . '/Backend/config/';
    
    if (file_exists($backendPath . 'database.php')) {
        require_once $backendPath . 'database.php';
    }
    
    if (file_exists($backendPath . 'queries.php')) {
        require_once $backendPath . 'queries.php';
    }
    
    if (file_exists($backendPath . 'security.php')) {
        require_once $backendPath . 'security.php';
    }

    // Shared site settings repository (getSetting/updateSetting below).
    if (file_exists($backendPath . 'site_settings.php')) {
        require_once $backendPath . 'site_settings.php';
    }

    // Editable website content (cms()/cms_img()/cms_text()) used by every page.
    if (file_exists($backendPath . 'cms.php')) {
        require_once $backendPath . 'cms.php';
    }
    
    // Defer the connection until the current page actually needs database data.
    // Keep the sentinel key unset so getDB() can connect on first use.
    unset($GLOBALS['pdo']);
} catch (Exception $e) {
    @error_log('Configuration error: ' . $e->getMessage());
}

// Helper function to get PDO instance
function getDB(): ?PDO {
    if (array_key_exists('pdo', $GLOBALS)) {
        if ($GLOBALS['pdo'] instanceof PDO) return $GLOBALS['pdo'];
        if ($GLOBALS['pdo'] === null && !empty($GLOBALS['pdo_connection_attempted'])) return null;
    }
    $GLOBALS['pdo_connection_attempted'] = true;
    $GLOBALS['pdo'] = getDatabaseConnection();
    if (!empty($GLOBALS['pdo'])) {
        // Auto-run new tables if missing — keeps the live site self-healing
        @require_once __DIR__ . '/../../Backend/config/auto_migrate.php';
        if (function_exists('ensureBusiaSchema')) {
            ensureBusiaSchema($GLOBALS['pdo']);
        }
    }
    return $GLOBALS['pdo'];
}

/**
 * Execute a SELECT query safely and return rows.
 */
function safeQueryAll(PDO $pdo, string $sql, array $params = []): array {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('SafeQueryAll failed: ' . $e->getMessage() . ' SQL: ' . $sql);
        return [];
    }
}

/**
 * Execute a scalar query safely and return a single value.
 */
function safeQueryScalar(PDO $pdo, string $sql, array $params = [], mixed $default = null): mixed {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    } catch (Exception $e) {
        error_log('SafeQueryScalar failed: ' . $e->getMessage() . ' SQL: ' . $sql);
        return $default;
    }
}

/**
 * Get site setting by key
 */
function getSetting(string $key, string $default = ''): string {
    try {
        $pdo = getDB();
        if (!$pdo) return $default;
        if (function_exists('siteSettingGet')) {
            return siteSettingGet($pdo, $key, $default);
        }
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (string)$result : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Update site setting
 */
function updateSetting(string $key, string $value): bool {
    $pdo = getDB();
    if (!$pdo) return false;
    if (function_exists('siteSettingSet')) {
        return siteSettingSet($pdo, $key, $value);
    }
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

// Use the application's configured timezone without requiring a DB request.
@date_default_timezone_set('Africa/Nairobi');
