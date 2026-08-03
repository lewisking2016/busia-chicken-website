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
        'order_number' => $order_number,
        'total_amount' => $total_amount,
        'payment_method' => $payment_method,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $response['success'] = true;
    $response['message'] = 'Order created successfully';
    $response['data'] = [
        'order_number' => $order_number,
        'total_amount' => $total_amount,
        'payment_method' => $payment_method
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
