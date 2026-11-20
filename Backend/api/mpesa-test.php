<?php
/**
 * M-Pesa Connection Test
 * Tests that the saved Daraja credentials can generate a valid OAuth token.
 * Called by the admin M-Pesa Setup panel → "Test Connection" button.
 */
declare(strict_types=1);

header('Content-Type: application/json');

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

require_once __DIR__ . '/../config/database.php';

// Only allow admin users
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    require_once __DIR__ . '/../config/site_settings.php';
    $settings = busiaMpesaSettings($pdo);

    $consumerKey    = $settings['mpesa_consumer_key']    ?? '';
    $consumerSecret = $settings['mpesa_consumer_secret'] ?? '';
    $environment    = $settings['mpesa_environment']     ?? 'sandbox';

    if (!$consumerKey || !$consumerSecret) {
        echo json_encode(['success' => false, 'message' => 'Consumer Key and Consumer Secret are not set. Save your credentials first.']);
        exit;
    }

    $baseUrl = $environment === 'production'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke';

    $credentials = base64_encode($consumerKey . ':' . $consumerSecret);

    $ch = curl_init($baseUrl . '/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $credentials],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => $environment === 'production',
    ]);
    $resp  = curl_exec($ch);
    $err   = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) {
        echo json_encode(['success' => false, 'message' => 'Network error: ' . $err]);
        exit;
    }

    $data = json_decode($resp, true);
    if (isset($data['access_token']) && $data['access_token']) {
        $envLabel = $environment === 'production' ? 'Production' : 'Sandbox';
        echo json_encode([
            'success' => true,
            'message' => "Connected to Daraja {$envLabel} API successfully. Token received."
        ]);
    } else {
        $errMsg = $data['errorMessage'] ?? $data['error_description'] ?? $resp;
        echo json_encode(['success' => false, 'message' => 'Auth failed: ' . $errMsg]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
