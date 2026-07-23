<?php
/**
 * Centralized Admin Actions API
 * Handles report exports and other utility functions.
 */
declare(strict_types=1);

header('Content-Type: application/json');

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

// Admin access check
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager'], true)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../Frontend/includes/config.php';
$pdo = getDB();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'export_orders':
            // Generate CSV for orders
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="busia_orders_report_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Order ID', 'Order Number', 'Customer', 'Email', 'Amount', 'Status', 'Date']);
            
            $stmt = $pdo->query("SELECT o.id, o.order_number, u.username, u.email, o.total_amount, o.status, o.created_at 
                                 FROM orders o 
                                 LEFT JOIN users u ON o.user_id = u.id 
                                 ORDER BY o.created_at DESC");
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;

        case 'get_order_details':
            $order_id = (int)($_GET['order_id'] ?? 0);
            if (!$order_id) throw new Exception("Order ID required");

            $order = fetchOne($pdo, "SELECT o.*, u.username, u.email, u.phone_number as user_phone 
                                    FROM orders o 
                                    LEFT JOIN users u ON o.user_id = u.id 
                                    WHERE o.id = ?", [$order_id]);
            
            if (!$order) throw new Exception("Order not found");

            $items = fetchAll($pdo, "SELECT oi.*, p.name as product_name, p.product_type 
                                    FROM order_items oi 
                                    JOIN products p ON oi.product_id = p.id 
                                    WHERE oi.order_id = ?", [$order_id]);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'items' => $items
                ]
            ]);
            break;

        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
