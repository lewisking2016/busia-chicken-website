<?php
/**
 * Admin - Order Management (Full functionality)
 * Premium Minimalist Redesign
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

$path_prefix = '../../';
$page_title = 'Manage Orders - Admin';

include __DIR__ . '/includes/admin_header.php';

// Check admin access
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager'], true)) {
    echo "<script>window.location.href = '/busiaadmin';</script>";
    exit;
}

$pdo = getDB();
$orders = [];
$success_message = '';
$error_message = '';

// Handle Status Update Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    
    $valid_statuses = ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled'];
    if (in_array($new_status, $valid_statuses, true)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            $success_message = "Order status updated successfully.";
        } catch (Exception $e) {
            $error_message = "Failed to update order status: " . $e->getMessage();
        }
    } else {
        $error_message = "Invalid status selected.";
    }
}

// Handle Search and Filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build Query
if ($pdo) {
    try {
        $query = "SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (o.order_number LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
            $search_param = "%$search%";
            array_push($params, $search_param, $search_param, $search_param);
        }

        if (!empty($status_filter)) {
            $query .= " AND o.status = ?";
            $params[] = $status_filter;
        }

        $query .= " ORDER BY o.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Admin orders fetch error: " . $e->getMessage());
    }
}
?>

<!-- Alerts -->
<?php if ($success_message): ?>
<div style="padding: 12px 20px; background: #dcfce7; border: 1px solid #bbf7d0; border-radius: 4px; color: #15803d; font-size: 0.9rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
    <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
    <?php echo htmlspecialchars($success_message); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding: 12px 20px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 4px; color: #b91c1c; font-size: 0.9rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
    <i data-lucide="alert-circle" style="width: 16px; height: 16px;"></i>
    <?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<!-- Main Admin Content Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.5rem; color: var(--admin-text-heading);">Orders Dashboard</h2>
        <p style="margin: 4px 0 0 0; font-size: 0.875rem; color: #475569;">Monitor sales transactions and manage order fulfillment states.</p>
    </div>
</div>

<div class="admin-card" style="padding: 0; overflow: hidden; border-radius: 4px;">
    <!-- Search & Filter Bar -->
    <form method="GET" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; background: #fafafa; border-bottom: 1px solid var(--admin-border); flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px;">
            <i data-lucide="search" style="width: 18px; height: 18px; color: #94a3b8;"></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search orders by number or customer..." style="background: transparent; border: none; outline: none; font-size: 0.9rem; width: 100%;">
        </div>
        <div style="display: flex; gap: 8px;">
            <select name="status" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; background: #ffffff;">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-outline" style="border-radius: 4px; padding: 6px 16px; font-size: 0.85rem;">Filter</button>
            <?php if ($search || $status_filter): ?>
                <a href="orders.php" style="padding: 6px 12px; font-size: 0.85rem; color: #64748b; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Orders Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer Info</th>
                    <th>Order Date</th>
                    <th>Amount Paid</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                <?php 
                    $status_class = 'badge-pill-warning';
                    switch(strtolower($order['status'])) {
                        case 'completed': $status_class = 'badge-pill-success'; break;
                        case 'paid': $status_class = 'badge-pill-success'; break;
                        case 'processing': $status_class = 'badge-pill-warning'; break;
                        case 'shipped': $status_class = 'badge-pill-success'; break;
                        case 'pending': $status_class = 'badge-pill-warning'; break;
                        case 'cancelled': $status_class = 'badge-pill-danger'; break;
                    }
                ?>
                <tr>
                    <td style="font-family: monospace; font-weight: 600; color: var(--admin-primary);">
                        #<?php echo htmlspecialchars($order['order_number']); ?>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--admin-text-heading);"><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></div>
                        <div style="font-size: 0.8rem; color: #475569;"><?php echo htmlspecialchars($order['email'] ?? ''); ?></div>
                    </td>
                    <td style="color: #475569; font-size: 0.9rem;">
                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                    </td>
                    <td style="font-weight: 600; color: var(--admin-text-heading);">
                        KES <?php echo number_format((float)$order['total_amount']); ?>
                    </td>
                    <td style="color: #475569; font-size: 0.9rem; text-transform: uppercase;">
                        <?php echo htmlspecialchars($order['payment_method']); ?>
                    </td>
                    <td>
                        <span class="badge-pill <?php echo $status_class; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                            <button class="btn btn-trans btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                            </button>
                            <!-- Update Status Action Inline Dropdown -->
                            <form method="POST" style="margin: 0; display: inline-flex; align-items: center;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.8rem; outline: none; background: #ffffff;">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">No orders found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Order Details Modal -->
<div id="order-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="modal-content" style="background: #ffffff; padding: 0; border-radius: 4px; width: 100%; max-width: 750px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); max-height: 90vh; overflow: hidden; display: flex; flex-direction: column;">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 24px 32px; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
            <div>
                <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.25rem; color: var(--admin-text-heading);">Order Details</h3>
                <p id="modal-order-number-subtitle" style="margin: 4px 0 0 0; font-size: 0.85rem; color: var(--admin-primary); font-weight: 700;"></p>
            </div>
            <button class="btn btn-trans" onclick="closeOrderModal()" style="padding: 8px; border-radius: 4px; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div id="order-details-content" style="padding: 32px; overflow-y: auto; flex: 1;">
            <!-- Populated via JS -->
        </div>

        <!-- Modal Footer -->
        <div style="display: flex; justify-content: flex-end; padding: 20px 32px; border-top: 1px solid #f1f5f9; background: #f8fafc;">
            <button type="button" class="btn btn-primary" onclick="closeOrderModal()" style="padding: 10px 24px; border-radius: 4px; font-weight: 600; font-size: 0.9rem;">Close Details</button>
        </div>
    </div>
</div>

<script>
async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`/Backend/api/admin_actions.php?action=get_order_details&order_id=${orderId}`);
        const result = await response.json();
        
        if (!result.success) {
            alert(result.message);
            return;
        }

        const { order, items } = result.data;
        document.getElementById('modal-order-number-subtitle').textContent = 'Reference: #' + order.order_number;
        
        let html = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
                <div style="background: #fdfcf6; border: 1px solid #fef3c7; padding: 20px; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <i data-lucide="user" style="width: 16px; height: 16px; color: #d97706;"></i>
                        <h4 style="margin: 0; font-size: 0.85rem; color: #92400e; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Customer Info</h4>
                    </div>
                    <p style="margin: 0; font-weight: 700; color: #1e293b; font-size: 1rem;">${order.username || 'Guest Customer'}</p>
                    <p style="margin: 4px 0 0 0; font-size: 0.9rem; color: #475569;">${order.email || 'No email provided'}</p>
                    <p style="margin: 8px 0 0 0; font-size: 0.9rem; font-weight: 600; color: var(--admin-primary);">${order.phone_contact}</p>
                </div>
                <div style="background: #f0fdf4; border: 1px solid #dcfce7; padding: 20px; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <i data-lucide="map-pin" style="width: 16px; height: 16px; color: #16a34a;"></i>
                        <h4 style="margin: 0; font-size: 0.85rem; color: #166534; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Shipping To</h4>
                    </div>
                    <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: #1e293b; font-weight: 500;">${order.shipping_address}</p>
                </div>
            </div>

            <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="shopping-cart" style="width: 18px; height: 18px; color: #64748b;"></i>
                <h4 style="margin: 0; font-size: 0.9rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Order Summary</h4>
            </div>
            
            <div style="border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="padding: 14px 20px; text-align: left; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0;">Product Item</th>
                            <th style="padding: 14px 20px; text-align: center; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0;">Qty</th>
                            <th style="padding: 14px 20px; text-align: right; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0;">Unit Price</th>
                            <th style="padding: 14px 20px; text-align: right; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map(item => `
                            <tr>
                                <td style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9;">
                                    <div style="font-weight: 600; color: #1e293b;">${item.product_name}</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">${item.product_type.replace('_', ' ')}</div>
                                </td>
                                <td style="padding: 14px 20px; text-align: center; color: #1e293b; border-bottom: 1px solid #f1f5f9;">${item.quantity}</td>
                                <td style="padding: 14px 20px; text-align: right; color: #475569; border-bottom: 1px solid #f1f5f9;">KES ${Number(item.price_at_purchase).toLocaleString()}</td>
                                <td style="padding: 14px 20px; text-align: right; font-weight: 700; color: #1e293b; border-bottom: 1px solid #f1f5f9;">KES ${Number(item.price_at_purchase * item.quantity).toLocaleString()}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <div style="background: #f8fafc; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; color: #475569; font-size: 1rem;">Total Amount Paid</span>
                    <span style="font-size: 1.25rem; font-weight: 800; color: var(--admin-primary); font-family: 'Outfit', sans-serif;">KES ${Number(order.total_amount).toLocaleString()}</span>
                </div>
            </div>

            <div style="margin-top: 24px; padding: 16px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 4px; display: flex; align-items: flex-start; gap: 12px;">
                <i data-lucide="info" style="width: 20px; height: 20px; color: #d97706; flex-shrink: 0;"></i>
                <div>
                    <p style="margin: 0; font-size: 0.85rem; font-weight: 600; color: #92400e;">Payment Method: ${order.payment_method.toUpperCase()}</p>
                    <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: #b45309;">Transaction was recorded on ${new Date(order.created_at).toLocaleString()}</p>
                </div>
            </div>
        `;

        document.getElementById('order-details-content').innerHTML = html;
        document.getElementById('order-modal').style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (err) {
        console.error(err);
        alert('Failed to load order details.');
    }
}

function closeOrderModal() {
    document.getElementById('order-modal').style.display = 'none';
}
</script>

<?php
include __DIR__ . '/includes/admin_footer.php';
?>
