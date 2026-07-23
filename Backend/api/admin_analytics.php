<?php
/**
 * Admin Analytics API
 * Returns simple analytics data for admin dashboard charts
 */
declare(strict_types=1);

header('Content-Type: application/json');

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/queries.php';

$response = ['success' => false, 'data' => []];

// Require admin session
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

try {
    $pdo = getDatabaseConnection();

    // Sales last 7 days
    $salesStmt = $pdo->prepare("SELECT DATE(created_at) AS day, COALESCE(SUM(total_amount),0) AS total FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY DATE(created_at)");
    $salesStmt->execute();
    $sales = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Orders count last 7 days
    $ordersStmt = $pdo->prepare("SELECT DATE(created_at) AS day, COUNT(*) AS cnt FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY DATE(created_at)");
    $ordersStmt->execute();
    $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Top products by quantity sold
    $topStmt = $pdo->prepare("SELECT p.name, SUM(oi.quantity) AS qty FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY oi.product_id ORDER BY qty DESC LIMIT 5");
    $topStmt->execute();
    $topProducts = $topStmt->fetchAll(PDO::FETCH_ASSOC);

    // Inventory levels (low stock)
    $invStmt = $pdo->prepare("SELECT id, name, stock_quantity FROM products ORDER BY stock_quantity ASC LIMIT 10");
    $invStmt->execute();
    $inventory = $invStmt->fetchAll(PDO::FETCH_ASSOC);

    // Count of low stock alerts (stock <= 15)
    $alertCountStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM products WHERE stock_quantity <= 15");
    $alertCountStmt->execute();
    $alertCount = (int)($alertCountStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

    // Recent orders as system events/activity log
    $recentStmt = $pdo->prepare("SELECT o.id, u.first_name, u.last_name, o.total_amount, o.created_at, o.status FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
    $recentStmt->execute();
    $recentOrders = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = [
        'sales' => $sales,
        'orders' => $orders,
        'top_products' => $topProducts,
        'inventory' => $inventory,
        'alerts' => $alertCount,
        'recent_orders' => $recentOrders
    ];

} catch (Exception $e) {
    http_response_code(500);
    $response['success'] = false;
    $response['message'] = 'Server error';
    if (defined('APP_DEBUG') && APP_DEBUG) $response['error'] = $e->getMessage();
}

echo json_encode($response);

?>
