<?php
/**
 * Checkout API Endpoint
 * Processes orders and initiates M-Pesa payment
 */
declare(strict_types=1);

header('Content-Type: application/json');

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/site_settings.php';

$response = ['success' => false, 'message' => '', 'data' => []];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit;
}

try {
    // Verify CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        throw new Exception('Invalid security token');
    }

    // Validate cart
    if (empty($_SESSION['cart'])) {
        throw new Exception('Cart is empty');
    }

    // Get form data
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address) || empty($city)) {
        throw new Exception('All fields are required');
    }

    if (!isValidEmail($email)) {
        throw new Exception('Invalid email address');
    }

    if (!isValidPhone($phone)) {
        throw new Exception('Invalid phone number');
    }

    if (!in_array($payment_method, ['mpesa', 'bank', 'cod'], true)) {
        throw new Exception('Invalid payment method');
    }

    // Calculate totals from database
    $subtotal = 0;
    $order_items_data = [];

    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $pdo = getDatabaseConnection();
    $db_products = fetchAll($pdo, "SELECT * FROM products WHERE id IN ($placeholders)", $ids);
    
    $productMap = [];
    foreach ($db_products as $p) {
        $productMap[$p['id']] = $p;
    }

    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        if (!isset($productMap[$product_id])) {
            throw new Exception("Invalid product: {$product_id}");
        }

        $product = $productMap[$product_id];
        $item_total = (float)$product['price'] * $quantity;
        $subtotal += $item_total;

        $order_items_data[] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price_at_purchase' => (float)$product['price']
        ];
    }

    $delivery_charge = ($subtotal >= 5000) ? 0 : 500;
    $total_amount = $subtotal + $delivery_charge;

    // Generate order number
    $order_number = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);

    // Save order to database
    try {
        $pdo->beginTransaction();

        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $sql = "INSERT INTO orders (user_id, order_number, total_amount, payment_method, shipping_address, phone_contact, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $pdo->prepare($sql);
        $full_address = "$address, $city";
        $stmt->execute([$user_id, $order_number, $total_amount, $payment_method, $full_address, $phone]);
        $order_id = $pdo->lastInsertId();

        $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)";
        $item_stmt = $pdo->prepare($item_sql);
        foreach ($order_items_data as $item) {
            $item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price_at_purchase']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    // Store in session for confirmation page
    $_SESSION['last_order'] = [
        'order_id' => $order_id,
        'order_number' => $order_number,
        'total_amount' => $total_amount,
        'payment_method' => $payment_method,
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Attempt M-Pesa STK push if mpesa method selected and credentials are configured
    $mpesa_stk_sent = false;
    if ($payment_method === 'mpesa') {
        try {
            $sRows = busiaMpesaSettings($pdo);
            $cKey    = $sRows['mpesa_consumer_key']    ?? '';
            $cSec    = $sRows['mpesa_consumer_secret'] ?? '';
            $sCode   = $sRows['mpesa_shortcode']       ?? '';
            $pKey    = $sRows['mpesa_passkey']         ?? '';
            $mEnv    = $sRows['mpesa_environment']     ?? 'sandbox';
            $tType   = $sRows['mpesa_transaction_type'] ?? 'CustomerPayBillOnline';

            if ($cKey && $cSec && $sCode && $pKey) {
                $baseUrl = ($mEnv === 'production') ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';
                $callbackUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                    . '/Backend/api/mpesa-callback.php';

                // Generate OAuth token
                $auth = base64_encode($cKey . ':' . $cSec);
                $ch = curl_init($baseUrl . '/oauth/v1/generate?grant_type=client_credentials');
                curl_setopt_array($ch, [
                    CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $auth],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 10,
                    CURLOPT_SSL_VERIFYPEER => ($mEnv === 'production'),
                ]);
                $tokenResp = curl_exec($ch);
                curl_close($ch);
                $tokenData = json_decode($tokenResp ?: '{}', true);
                $accessToken = $tokenData['access_token'] ?? '';

                if ($accessToken) {
                    $ts = date('YmdHis');
                    $pwd = base64_encode($sCode . $pKey . $ts);
                    $normPhone = preg_replace('/\D/', '', $phone);
                    if (str_starts_with($normPhone, '0')) $normPhone = '254' . substr($normPhone, 1);
                    if (str_starts_with($normPhone, '+')) $normPhone = ltrim($normPhone, '+');

                    $stkPayload = [
                        'BusinessShortCode' => $sCode,
                        'Password'          => $pwd,
                        'Timestamp'         => $ts,
                        'TransactionType'   => $tType,
                        'Amount'            => (int)$total_amount,
                        'PartyA'            => $normPhone,
                        'PartyB'            => $sCode,
                        'PhoneNumber'       => $normPhone,
                        'CallBackURL'       => $callbackUrl,
                        'AccountReference'  => $order_number,
                        'TransactionDesc'   => 'Payment for ' . $order_number,
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
                        CURLOPT_TIMEOUT        => 12,
                        CURLOPT_SSL_VERIFYPEER => ($mEnv === 'production'),
                    ]);
                    $stkResp = curl_exec($ch);
                    curl_close($ch);
                    $stkData = json_decode($stkResp ?: '{}', true);

                    if (($stkData['ResponseCode'] ?? '') === '0') {
                        $mpesa_stk_sent = true;
                        $chkId = $stkData['CheckoutRequestID'] ?? '';
                        $merId = $stkData['MerchantRequestID'] ?? '';
                        try {
                            $pdo->prepare("INSERT INTO mpesa_transactions
                                (order_id, checkout_request_id, merchant_request_id, phone_number, amount, status, created_at)
                                VALUES (?, ?, ?, ?, ?, 'pending', NOW())")
                                ->execute([$order_id, $chkId, $merId, $normPhone, $total_amount]);
                        } catch (Exception $ex) {}
                    }
                }
            }
        } catch (Exception $e) {
            error_log('[Checkout M-Pesa] ' . $e->getMessage());
        }
    }

    $response['success'] = true;
    $response['message'] = $mpesa_stk_sent
        ? 'Order placed! STK prompt sent to your phone — please enter your M-Pesa PIN.'
        : 'Order placed successfully!';
    $response['data'] = [
        'order_id'       => $order_id,
        'order_number'   => $order_number,
        'total_amount'   => $total_amount,
        'payment_method' => $payment_method,
        'mpesa_stk_sent' => $mpesa_stk_sent,
    ];

    // Clear cart after successful order
    $_SESSION['cart'] = [];

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
    logSecurityEvent("Checkout error: " . $e->getMessage());
}

echo json_encode($response);
?>
