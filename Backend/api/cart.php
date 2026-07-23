<?php
/**
 * Cart API Endpoint
 * Handles add/remove/update operations via AJAX
 */
declare(strict_types=1);

header('Content-Type: application/json');

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

require_once '../config/database.php';

$pdo = getDatabaseConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => '', 'data' => []];

try {
    switch ($action) {
        case 'add':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);

            $product = fetchOne($pdo, "SELECT * FROM products WHERE id = ?", [$product_id]);
            if (!$product) {
                throw new Exception('Product not found');
            }

            if ($quantity < 1) {
                throw new Exception('Invalid quantity');
            }

            $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
            $response['success'] = true;
            $response['message'] = 'Product added to cart';
            $response['data']['cart_count'] = array_sum($_SESSION['cart']);
            break;

        case 'update':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 0);

            if (!isset($_SESSION['cart'][$product_id])) {
                throw new Exception('Product not in cart');
            }

            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
                $response['message'] = 'Product removed from cart';
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
                $response['message'] = 'Cart updated';
            }

            $response['success'] = true;
            $response['data']['cart_count'] = array_sum($_SESSION['cart']);
            break;

        case 'remove':
            $product_id = (int)($_POST['product_id'] ?? 0);

            if (!isset($_SESSION['cart'][$product_id])) {
                throw new Exception('Product not in cart');
            }

            unset($_SESSION['cart'][$product_id]);
            $response['success'] = true;
            $response['message'] = 'Product removed from cart';
            $response['data']['cart_count'] = array_sum($_SESSION['cart']);
            break;

        case 'get':
            $subtotal = 0;
            $items = [];

            if (!empty($_SESSION['cart'])) {
                $ids = array_keys($_SESSION['cart']);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $products = fetchAll($pdo, "SELECT * FROM products WHERE id IN ($placeholders)", $ids);
                
                $productMap = [];
                foreach ($products as $p) {
                    $productMap[$p['id']] = $p;
                }

                foreach ($_SESSION['cart'] as $product_id => $quantity) {
                    if (isset($productMap[$product_id])) {
                        $product = $productMap[$product_id];
                        $total = (float)$product['price'] * $quantity;
                        $subtotal += $total;

                        $items[] = [
                            'id' => $product_id,
                            'name' => $product['name'],
                            'price' => (float)$product['price'],
                            'quantity' => $quantity,
                            'total' => $total,
                            'product_type' => $product['product_type']
                        ];
                    }
                }
            }

            $delivery = ($subtotal >= 5000 || $subtotal == 0) ? 0 : 500;
            $response['success'] = true;
            $response['data'] = [
                'items' => $items,
                'subtotal' => $subtotal,
                'delivery' => $delivery,
                'total' => $subtotal + $delivery,
                'count' => array_sum($_SESSION['cart'])
            ];
            break;

        case 'clear':
            $_SESSION['cart'] = [];
            $response['success'] = true;
            $response['message'] = 'Cart cleared';
            $response['data']['cart_count'] = 0;
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
