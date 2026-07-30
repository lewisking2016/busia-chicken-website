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

        case 'bulk_update_status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid method");

            $csrfToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            if (function_exists('verifyCSRFToken') && !verifyCSRFToken($csrfToken)) {
                throw new Exception("Invalid security token");
            }

            $order_ids = json_decode($_POST['order_ids'] ?? '[]', true);
            $new_status = trim($_POST['status'] ?? '');
            $valid = ['pending','paid','picking','packing','production','dispatch','shipped','delivered','completed','cancelled'];

            if (empty($order_ids) || !is_array($order_ids)) throw new Exception("No orders selected");
            if (!in_array($new_status, $valid, true)) throw new Exception("Invalid status: $new_status");

            $pdo->beginTransaction();
            $updated = 0;
            foreach ($order_ids as $oid) {
                $oid = (int)$oid;
                if ($oid <= 0) continue;
                
                // Get current status to detect transitions
                $current = fetchOne($pdo, "SELECT status FROM orders WHERE id = ?", [$oid]);
                if (!$current) continue;
                
                $oldStatus = $current['status'];
                execute($pdo, "UPDATE orders SET status = ? WHERE id = ?", [$new_status, $oid]);
                $updated++;

                // Auto-deduct raw materials when a feed order transitions to a "fulfilled" state from non-fulfilled
                $nonFulfilled = ['pending', 'cancelled'];
                $fulfilled = ['paid','picking','packing','production','dispatch','shipped','delivered','completed'];
                
                if (in_array($oldStatus, $nonFulfilled, true) && in_array($new_status, $fulfilled, true)) {
                    // Get feed items in this order and deduct their raw materials
                    $feedItems = fetchAll($pdo, "
                        SELECT oi.product_id, oi.quantity, p.product_type
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ? AND p.product_type = 'feed'
                    ", [$oid]);

                    foreach ($feedItems as $fi) {
                        // Find the recipe for this product
                        $recipe = fetchOne($pdo, "SELECT * FROM feed_recipes WHERE product_id = ? AND is_active = 1 LIMIT 1", [$fi['product_id']]);
                        if (!$recipe) continue;

                        $ingredients = fetchAll($pdo, "
                            SELECT ri.amount_kg as base_amount, rm.id as rm_id, rm.name, rm.stock_tons, rm.current_price_per_ton
                            FROM recipe_ingredients ri
                            JOIN raw_materials rm ON ri.raw_material_id = rm.id
                            WHERE ri.recipe_id = ?
                        ", [$recipe['id']]);

                        $totalCost = 0;
                        foreach ($ingredients as $ing) {
                            $neededKg = $ing['base_amount'] * $fi['quantity'];
                            $neededTons = $neededKg / 1000;

                            // Deduct but don't go below zero
                            execute($pdo, "UPDATE raw_materials SET stock_tons = GREATEST(stock_tons - ?, 0) WHERE id = ?", [$neededTons, $ing['rm_id']]);
                            $totalCost += ($neededKg / 1000) * $ing['current_price_per_ton'];

                            // Check and create alert if low
                            $updated_rm = fetchOne($pdo, "SELECT stock_tons, min_stock_level, name FROM raw_materials WHERE id = ?", [$ing['rm_id']]);
                            if ($updated_rm && (float)$updated_rm['stock_tons'] <= (float)$updated_rm['min_stock_level']) {
                                execute($pdo, "INSERT IGNORE INTO stock_alerts (alert_type, message, related_id) VALUES ('low_stock', ?, ?)",
                                    ["{$updated_rm['name']} is running low after sale fulfillment! Stock: {$updated_rm['stock_tons']} tons.", $ing['rm_id']]);
                            }
                        }

                        // Also deduct finished product stock
                        execute($pdo, "UPDATE products SET stock_quantity = GREATEST(stock_quantity - ?, 0) WHERE id = ?", [$fi['quantity'], $fi['product_id']]);
                    }
                }
            }
            $pdo->commit();

            echo json_encode(['success' => true, 'message' => "Updated $updated orders to '$new_status'."]);
            break;

        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
