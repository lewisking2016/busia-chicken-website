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
define('SITE_NAME', 'Busia Chicken Farm');
define('SITE_TAGLINE', 'Premium Poultry Products & Farm Management');
define('SITE_DESCRIPTION', 'Leading poultry supplier in East Africa. Quality layers, broilers, day-old chicks, and premium feeds.');
define('SITE_EMAIL', 'info@busiachicken.com');
define('SITE_PHONE', '+254 727 585 599');
define('SITE_ADDRESS', 'Nasira AC Sub-location, Busibwabo Location, Busia County, Kenya');

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
    'live_chicken' => 'Live Chicken',
    'chicks' => 'Day-Old Chicks',
    'eggs' => 'Eggs',
    'feed' => 'Animal Feed'
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
    
    // Initialize Database Connection - NEVER let this fail
    if (function_exists('getDatabaseConnection')) {
        try {
            $GLOBALS['pdo'] = getDatabaseConnection();
        } catch (Exception $e) {
            @error_log('Database connection error: ' . $e->getMessage());
            $GLOBALS['pdo'] = null;
        }
    } else {
        $GLOBALS['pdo'] = null;
    }
} catch (Exception $e) {
    @error_log('Configuration error: ' . $e->getMessage());
    $GLOBALS['pdo'] = null;
}

// Helper function to get PDO instance
function getDB(): ?PDO {
    return $GLOBALS['pdo'] ?? null;
}

/**
 * Get site setting by key
 */
function getSetting(string $key, string $default = ''): string {
    try {
        $pdo = getDB();
        if (!$pdo) return $default;
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
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

// Apply live settings that affect runtime behavior.
try {
    $configuredTimezone = getSetting('timezone', 'Africa/Nairobi');
    if ($configuredTimezone && in_array($configuredTimezone, timezone_identifiers_list(), true)) {
        @date_default_timezone_set($configuredTimezone);
    } else {
        @date_default_timezone_set('Africa/Nairobi');
    }
} catch (Exception $e) {
    @date_default_timezone_set('Africa/Nairobi');
}
