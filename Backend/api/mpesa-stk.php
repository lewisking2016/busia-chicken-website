<?php
/**
 * M-Pesa STK Push API
 * Initiates Lipa na M-Pesa Online (STK Push) via Safaricom Daraja API.
 * Credentials are stored in the admin Settings table and can be configured
 * by the admin through the Settings → M-Pesa panel.
 */
declare(strict_types=1);

header('Content-Type: application/json');

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

$response = ['success' => false, 'message' => '', 'data' => []];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit;
}

try {
    // ── Load M-Pesa credentials (site settings win, legacy table falls back) ──
    $pdo = getDatabaseConnection();
    require_once __DIR__ . '/../config/site_settings.php';
    $settingsRows = busiaMpesaSettings($pdo);

    $consumerKey    = $settingsRows['mpesa_consumer_key']    ?? '';
    $consumerSecret = $settingsRows['mpesa_consumer_secret'] ?? '';
    $shortcode      = $settingsRows['mpesa_shortcode']       ?? '';
    $passkey        = $settingsRows['mpesa_passkey']         ?? '';
    $environment    = $settingsRows['mpesa_environment']     ?? 'sandbox'; // 'sandbox' or 'production'
    $transactionType = $settingsRows['mpesa_transaction_type'] ?? 'CustomerPayBillOnline'; // or CustomerBuyGoodsOnline

    if (!$consumerKey || !$consumerSecret || !$shortcode || !$passkey) {
        throw new Exception('M-Pesa is not configured yet. Please go to Admin → Settings → M-Pesa Setup and enter your Daraja API credentials.');
    }

    // ── Request inputs ───────────────────────────────────────────────────
    $phone  = sanitizeInput($_POST['phone']    ?? '');
    $amount = (int)($_POST['amount']           ?? 0);
    $orderId = (int)($_POST['order_id']        ?? 0);
    $orderRef = sanitizeInput($_POST['order_number'] ?? 'ORDER-' . $orderId);

    if (!$phone || $amount < 1) {
        throw new Exception('Phone number and amount are required.');
    }

    // Normalise phone: 07XX → 2547XX, +2547XX → 2547XX
    $phone = preg_replace('/\D/', '', $phone);
    if (str_starts_with($phone, '0')) {
        $phone = '254' . substr($phone, 1);
    } elseif (str_starts_with($phone, '+')) {
        $phone = ltrim($phone, '+');
    }
    if (!preg_match('/^2547\d{8}$/', $phone)) {
        throw new Exception('Invalid Kenyan phone number. Use format 07XXXXXXXX or 2547XXXXXXXX.');
    }

    // ── API URLs ─────────────────────────────────────────────────────────
    $baseUrl = $environment === 'production'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke';

    $callbackUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        . '/Backend/api/mpesa-callback.php';

    // ── Step 1: Get OAuth access token ───────────────────────────────────
    $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
    $ch = curl_init($baseUrl . '/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $credentials],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => $environment === 'production',
    ]);
    $tokenResponse = curl_exec($ch);
    $tokenError    = curl_error($ch);
    curl_close($ch);

    if ($tokenError) {
        throw new Exception('Could not reach Safaricom API: ' . $tokenError);
    }

    $tokenData = json_decode($tokenResponse, true);
    $accessToken = $tokenData['access_token'] ?? '';
    if (!$accessToken) {
        throw new Exception('Safaricom returned no access token. Check your Consumer Key and Secret. Response: ' . $tokenResponse);
    }

    // ── Step 2: STK Push ─────────────────────────────────────────────────
    $timestamp = date('YmdHis');
    $password  = base64_encode($shortcode . $passkey . $timestamp);

    $stkPayload = [
        'BusinessShortCode' => $shortcode,
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'TransactionType'   => $transactionType,
        'Amount'            => $amount,
        'PartyA'            => $phone,
        'PartyB'            => $shortcode,
        'PhoneNumber'       => $phone,
        'CallBackURL'       => $callbackUrl,
        'AccountReference'  => $orderRef,
        'TransactionDesc'   => 'Payment for ' . $orderRef,
    ];

    $ch = curl_init($baseUrl . '/mpesa/stkpush/v1/processrequest');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($stkPayload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => $environment === 'production',
    ]);
    $stkResponse = curl_exec($ch);
    $stkError    = curl_error($ch);
    curl_close($ch);

    if ($stkError) {
        throw new Exception('STK Push request failed: ' . $stkError);
    }

    $stkData = json_decode($stkResponse, true);

    if (($stkData['ResponseCode'] ?? '') === '0') {
        // ── Log the pending transaction ──────────────────────────────
        $checkoutRequestId = $stkData['CheckoutRequestID'] ?? '';
        $merchantRequestId = $stkData['MerchantRequestID'] ?? '';

        if ($pdo && $orderId) {
            try {
                $pdo->prepare("INSERT INTO mpesa_transactions
                    (order_id, checkout_request_id, merchant_request_id, phone_number, amount, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'pending', NOW())")
                    ->execute([$orderId, $checkoutRequestId, $merchantRequestId, $phone, $amount]);
            } catch (Exception $e) {
                // Non-fatal — log but don't block the response
                error_log('[M-Pesa] Failed to log transaction: ' . $e->getMessage());
            }
        }

        $response['success'] = true;
        $response['message'] = 'STK Push sent! Check your phone and enter your M-Pesa PIN.';
        $response['data']    = [
            'checkout_request_id' => $checkoutRequestId,
            'merchant_request_id' => $merchantRequestId,
        ];
    } else {
        $errorMessage = $stkData['errorMessage'] ?? $stkData['ResponseDescription'] ?? $stkResponse;
        throw new Exception('STK Push failed: ' . $errorMessage);
    }

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
    error_log('[M-Pesa STK] ' . $e->getMessage());
}

echo json_encode($response);
