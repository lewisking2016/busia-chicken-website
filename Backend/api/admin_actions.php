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

        case 'list_orders':
            $status = $_GET['status'] ?? null;
            $from = $_GET['from'] ?? null;
            $to = $_GET['to'] ?? null;
            $sql = "SELECT o.*, u.username, u.email, u.first_name, u.last_name, u.phone_number
                    FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE 1=1";
            $params = [];
            if ($status) { $sql .= " AND o.status=?"; $params[] = $status; }
            if ($from) { $sql .= " AND DATE(o.created_at) >= ?"; $params[] = $from; }
            if ($to) { $sql .= " AND DATE(o.created_at) <= ?"; $params[] = $to; }
            $sql .= " ORDER BY o.created_at DESC LIMIT 500";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['customer_name']  = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: ($r['username'] ?? 'Guest');
                $r['customer_email'] = $r['email'] ?? '';
                $r['phone_contact']  = $r['phone_contact'] ?? $r['phone_number'] ?? '';
            }
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        case 'get_order':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception('Order ID required');
            $order = fetchOne($pdo, "SELECT o.*, u.username, u.email, u.first_name, u.last_name
                                      FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.id=?", [$id]);
            if (!$order) throw new Exception('Order not found');
            $items = fetchAll($pdo, "SELECT oi.*, p.name FROM order_items oi
                                     JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?", [$id]);
            $order['customer_name']  = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')) ?: ($order['username'] ?? 'Guest');
            $order['customer_email'] = $order['email'] ?? '';
            $order['items'] = $items;
            echo json_encode(['success' => true, 'data' => $order]);
            break;

        case 'update_order_status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid method');
            $id = (int)($_POST['id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            $valid = ['pending','paid','processing','shipped','completed','cancelled'];
            if (!$id) throw new Exception('ID required');
            if (!in_array($status, $valid, true)) throw new Exception('Invalid status');
            execute($pdo, "UPDATE orders SET status=? WHERE id=?", [$status, $id]);
            echo json_encode(['success' => true, 'message' => 'Status updated']);
            break;

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

                    // --- Direct Raw Material Sales: Deduct kgs from raw_materials.stock_tons ---
                    $rawMaterialItems = fetchAll($pdo, "
                        SELECT oi.product_id, oi.quantity, p.raw_material_id
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ? AND p.raw_material_id IS NOT NULL
                    ", [$oid]);

                    foreach ($rawMaterialItems as $rmi) {
                        $rm = fetchOne($pdo, "SELECT id, name, stock_tons, reserved_production_kg, min_stock_level FROM raw_materials WHERE id = ?", [$rmi['raw_material_id']]);
                        if (!$rm) continue;

                        $deductKg = (float)$rmi['quantity']; // Each unit sold = 1 kg of raw material
                        $currentStock = (float)$rm['stock_tons'];
                        $reserve = (float)$rm['reserved_production_kg'];
                        $availableForSale = max(0, $currentStock - $reserve);

                        // Deduct stock (enforce floor at zero, not the reserve — the reserve is a warning threshold)
                        execute($pdo, "UPDATE raw_materials SET stock_tons = GREATEST(stock_tons - ?, 0) WHERE id = ?", [$deductKg, $rmi['raw_material_id']]);

                        // Also deduct finished product stock
                        execute($pdo, "UPDATE products SET stock_quantity = GREATEST(stock_quantity - ?, 0) WHERE id = ?", [$rmi['quantity'], $rmi['product_id']]);

                        // If this sale breaches the safety production reserve, create a margin_protection alert
                        if ($deductKg > $availableForSale) {
                            execute($pdo, "INSERT INTO stock_alerts (alert_type, message, related_id) VALUES ('margin_protection', ?, ?)",
                                ["{$rm['name']} raw material sale has breached the safety production reserve! Remaining: " . max(0, $currentStock - $deductKg) . " kgs (Reserve floor: {$reserve} kgs).", $rmi['raw_material_id']]);
                        }

                        // Low stock alert
                        $newStock = max(0, $currentStock - $deductKg);
                        if ($newStock <= (float)$rm['min_stock_level']) {
                            execute($pdo, "INSERT INTO stock_alerts (alert_type, message, related_id) VALUES ('low_stock', ?, ?)",
                                ["{$rm['name']} is critically low after direct sale! Stock: {$newStock} kgs.", $rmi['raw_material_id']]);
                        }
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
