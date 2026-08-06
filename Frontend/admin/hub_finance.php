<?php
/**
 * Hub: Sales & Finance — Orders, Sales, Payments, Expenses, Reports
 */
declare(strict_types=1);
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();
$page_title = 'Sales & Finance - Admin';
include __DIR__ . '/includes/admin_header.php';

$tab = $_GET['tab'] ?? 'orders';
$validTabs = ['orders','sales','payments','expenses','reports'];
if (!in_array($tab, $validTabs, true)) $tab = 'orders';

$pdo = getDB();
$message = ''; $error_message = '';

/* ── Handle POST ─────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $postAction = $_POST['_action'] ?? '';

    /* Update order status */
    if ($postAction === 'update_order_status') {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');
        $valid = ['pending','paid','processing','shipped','completed','cancelled'];
        if (in_array($newStatus, $valid, true)) {
            try {
                $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$newStatus, $orderId]);
                $message = 'Order status updated.';
            } catch (Exception $e) { $error_message = $e->getMessage(); }
        }
        $tab = 'orders';
    }

    /* Save payment */
    if ($postAction === 'save_payment') {
        $id   = (int)($_POST['id'] ?? 0);
        $cat  = trim($_POST['category'] ?? '');
        $amt  = (float)($_POST['amount'] ?? 0);
        $meth = trim($_POST['method'] ?? 'Cash');
        $stat = trim($_POST['status'] ?? 'Pending');
        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $desc = trim($_POST['description'] ?? '');
        if ($cat && $amt > 0) {
            try {
                if ($id > 0) {
                    $pdo->prepare('UPDATE financial_records SET category=?,amount=?,payment_method=?,payment_status=?,transaction_date=?,description=? WHERE id=?')
                        ->execute([$cat,$amt,$meth,$stat,$date,$desc,$id]);
                    $message = 'Payment updated.';
                } else {
                    $pdo->prepare('INSERT INTO financial_records (type,category,amount,payment_method,payment_status,transaction_date,description) VALUES ("income",?,?,?,?,?,?)')
                        ->execute([$cat,$amt,$meth,$stat,$date,$desc]);
                    $message = 'Payment recorded.';
                }
            } catch (Exception $e) { $error_message = $e->getMessage(); }
        } else { $error_message = 'Category and amount are required.'; }
        $tab = 'payments';
    }
}

/* ── Load tab data ─────────────────────────────────── */
$orders = $sales = $payments = [];
$orderSearch = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

if ($pdo) {
    try {
        if ($tab === 'orders') {
            $q = 'SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1';
            $params = [];
            if ($orderSearch) { $q .= ' AND (o.order_number LIKE ? OR u.username LIKE ?)'; $params[] = "%$orderSearch%"; $params[] = "%$orderSearch%"; }
            if ($statusFilter) { $q .= ' AND o.status = ?'; $params[] = $statusFilter; }
            $q .= ' ORDER BY o.created_at DESC LIMIT 200';
            $stmt = $pdo->prepare($q); $stmt->execute($params);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($tab === 'sales') {
            $orders = $pdo->query('SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.status IN ("completed","paid") ORDER BY o.created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($tab === 'payments') {
            $payments = $pdo->query('SELECT * FROM financial_records WHERE type="income" ORDER BY transaction_date DESC, created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) { $error_message = $e->getMessage(); }
}

// Summary stats for reports tab
$stats = [];
if ($tab === 'reports' && $pdo) {
    try {
        $stats['total_orders']   = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $stats['total_revenue']  = $pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ("completed","paid")')->fetchColumn();
        $stats['total_expenses'] = $pdo->query('SELECT COALESCE(SUM(amount),0) FROM financial_records WHERE type="expense"')->fetchColumn();
        $stats['pending_orders'] = $pdo->query('SELECT COUNT(*) FROM orders WHERE status="pending"')->fetchColumn();
        $recentSales = $pdo->query('SELECT DATE(created_at) AS day, SUM(total_amount) AS total FROM orders WHERE status IN ("completed","paid") AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY day ASC')->fetchAll(PDO::FETCH_ASSOC);
        $stats['recent_sales'] = $recentSales;
    } catch (Exception $e) { $stats = []; }
}

$tabs = [
    'orders'   => ['icon' => 'shopping-bag',   'label' => 'Orders'],
    'sales'    => ['icon' => 'receipt',         'label' => 'Sales'],
    'payments' => ['icon' => 'wallet',          'label' => 'Payments'],
    'expenses' => ['icon' => 'minus-circle',    'label' => 'Expenses'],
    'reports'  => ['icon' => 'bar-chart-3',     'label' => 'Reports'],
];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;">Sales & Finance</h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:0.9rem;">Track orders, payments, expenses, and generate financial reports.</p>
    </div>
</div>

<?php if ($message): ?>
<div style="padding:13px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:8px;color:#166534;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i> <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:13px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:8px;color:#b91c1c;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <i data-lucide="alert-circle" style="width:18px;height:18px;"></i> <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<!-- Tab Bar -->
<div style="display:flex;gap:4px;background:#f1f5f9;padding:5px;border-radius:10px;margin-bottom:24px;overflow-x:auto;scrollbar-width:none;">
<?php foreach ($tabs as $key => $info): ?>
    <a href="?tab=<?php echo $key; ?>"
       style="display:flex;align-items:center;gap:7px;padding:9px 16px;border-radius:7px;text-decoration:none;white-space:nowrap;font-weight:600;font-size:0.86rem;transition:all 0.2s;
              <?php echo $tab === $key ? 'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);' : 'color:#64748b;'; ?>">
        <i data-lucide="<?php echo $info['icon']; ?>" style="width:15px;height:15px;"></i>
        <?php echo $info['label']; ?>
    </a>
<?php endforeach; ?>
</div>

<!-- ══════ ORDERS TAB ══════ -->
<?php if ($tab === 'orders'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">All Orders</h3>
        <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
            <input type="hidden" name="tab" value="orders">
            <input type="text" name="q" value="<?php echo htmlspecialchars($orderSearch, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search order / customer…" style="padding:9px 14px;border:1.5px solid #e2e8f0;border-radius:6px;font-size:0.88rem;outline:none;">
            <select name="status" style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:6px;font-size:0.88rem;">
                <option value="">All Statuses</option>
                <?php foreach (['pending','paid','processing','shipped','completed','cancelled'] as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $statusFilter===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-outline"><i data-lucide="search" style="width:15px;height:15px;"></i> Filter</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Order #</th><th>Customer</th><th>Total (KES)</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="6" style="text-align:center;padding:28px;color:#94a3b8;">No orders found.</td></tr>
            <?php else: foreach ($orders as $o): ?>
                <tr>
                    <td><strong>#<?php echo htmlspecialchars($o['order_number'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><?php echo htmlspecialchars($o['username'] ?: ($o['email'] ?? 'Guest'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo number_format((float)$o['total_amount'], 2); ?></td>
                    <td>
                        <?php
                        $sc = ['pending'=>'badge-pill-warning','paid'=>'badge-pill-success','completed'=>'badge-pill-success','cancelled'=>'badge-pill-danger','processing'=>'badge-pill-warning','shipped'=>'badge-pill-warning'];
                        $cls = $sc[$o['status']] ?? 'badge-pill-warning';
                        ?>
                        <span class="badge-pill <?php echo $cls; ?>"><?php echo ucfirst(htmlspecialchars($o['status'], ENT_QUOTES, 'UTF-8')); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars(substr($o['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <div class="tbl-actions">
                            <button class="btn btn-info btn-sm" onclick="openOrderModal(<?php echo (int)$o['id']; ?>, '<?php echo htmlspecialchars($o['order_number'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($o['status'], ENT_QUOTES, 'UTF-8'); ?>')">
                                <i data-lucide="edit-3" style="width:13px;height:13px;"></i> Update Status
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Order Status Modal -->
<div id="order-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;width:100%;max-width:400px;box-shadow:0 20px 40px rgba(0,0,0,0.15);">
        <h3 style="margin:0 0 8px;font-family:'Outfit',sans-serif;">Update Order Status</h3>
        <p id="order-modal-label" style="margin:0 0 20px;color:#64748b;font-size:0.9rem;"></p>
        <form method="POST">
            <input type="hidden" name="_action" value="update_order_status">
            <input type="hidden" name="order_id" id="order-modal-id">
            <div class="admin-form-group">
                <label class="admin-form-label">New Status</label>
                <select class="admin-form-control" name="status" id="order-modal-status">
                    <?php foreach (['pending','paid','processing','shipped','completed','cancelled'] as $s): ?>
                    <option value="<?php echo $s; ?>"><?php echo ucfirst($s); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="document.getElementById('order-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="check" style="width:15px;height:15px;"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════ SALES TAB ══════ -->
<?php elseif ($tab === 'sales'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Completed Sales</h3>
            <p style="margin:4px 0 0;font-size:0.85rem;color:#64748b;">All orders that have been paid or completed.</p>
        </div>
    </div>
    <?php
    $totalRevenue = array_sum(array_column($orders, 'total_amount'));
    ?>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;">
        <div class="stat-card"><div class="stat-card-info"><small>Total Completed</small><strong><?php echo count($orders); ?></strong></div><div class="stat-card-icon"><i data-lucide="receipt" style="width:24px;height:24px;"></i></div></div>
        <div class="stat-card"><div class="stat-card-info"><small>Total Revenue</small><strong>KES <?php echo number_format($totalRevenue, 0); ?></strong></div><div class="stat-card-icon accent"><i data-lucide="trending-up" style="width:24px;height:24px;"></i></div></div>
        <div class="stat-card"><div class="stat-card-info"><small>Average Sale</small><strong>KES <?php echo count($orders) ? number_format($totalRevenue / count($orders), 0) : '0'; ?></strong></div><div class="stat-card-icon info"><i data-lucide="bar-chart-2" style="width:24px;height:24px;"></i></div></div>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Order #</th><th>Customer</th><th>Amount (KES)</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="5" style="text-align:center;padding:28px;color:#94a3b8;">No completed sales yet.</td></tr>
            <?php else: foreach ($orders as $o): ?>
                <tr>
                    <td><strong>#<?php echo htmlspecialchars($o['order_number'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><?php echo htmlspecialchars($o['username'] ?? 'Guest', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo number_format((float)$o['total_amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars(substr($o['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge-pill badge-pill-success"><?php echo ucfirst(htmlspecialchars($o['status'], ENT_QUOTES, 'UTF-8')); ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ══════ PAYMENTS TAB ══════ -->
<?php elseif ($tab === 'payments'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Income & Payments</h3>
            <p style="margin:4px 0 0;font-size:0.85rem;color:#64748b;">Log and manage all incoming payments and collections.</p>
        </div>
        <button class="btn btn-primary" onclick="openPaymentModal()">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Add Payment
        </button>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Date</th><th>Category</th><th>Amount (KES)</th><th>Method</th><th>Status</th><th>Notes</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="7" style="text-align:center;padding:28px;color:#94a3b8;">No payment records yet.</td></tr>
            <?php else: foreach ($payments as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['transaction_date'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($p['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><strong><?php echo number_format((float)$p['amount'], 2); ?></strong></td>
                    <td><?php echo htmlspecialchars($p['payment_method'] ?? 'Cash', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge-pill <?php echo ($p['payment_status'] ?? '') === 'Approved' ? 'badge-pill-success' : 'badge-pill-warning'; ?>"><?php echo htmlspecialchars($p['payment_status'] ?? 'Pending', ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($p['description'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <div class="tbl-actions">
                            <button class="btn btn-trans btn-sm" onclick='openPaymentModal(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8"); ?>)'>
                                <i data-lucide="pencil" style="width:13px;height:13px;"></i> Edit
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Payment Modal -->
<div id="payment-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;width:100%;max-width:520px;box-shadow:0 20px 40px rgba(0,0,0,0.15);">
        <h3 id="payment-modal-title" style="margin:0 0 22px;font-family:'Outfit',sans-serif;">Add Payment</h3>
        <form method="POST">
            <input type="hidden" name="_action" value="save_payment">
            <input type="hidden" name="id" id="pay-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="admin-form-group"><label class="admin-form-label">Category</label><input class="admin-form-control" name="category" id="pay-cat" placeholder="e.g. Farm Sales" required></div>
                <div class="admin-form-group"><label class="admin-form-label">Amount (KES)</label><input class="admin-form-control" type="number" step="0.01" name="amount" id="pay-amt" required></div>
                <div class="admin-form-group"><label class="admin-form-label">Payment Method</label>
                    <select class="admin-form-control" name="method" id="pay-meth">
                        <?php foreach (['Cash','M-Pesa','Bank Transfer','Card','Cheque'] as $pm): ?><option><?php echo $pm; ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group"><label class="admin-form-label">Status</label>
                    <select class="admin-form-control" name="status" id="pay-stat">
                        <?php foreach (['Pending','Approved','Completed','Failed'] as $ps): ?><option><?php echo $ps; ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group"><label class="admin-form-label">Date</label><input class="admin-form-control" type="date" name="date" id="pay-date" value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="admin-form-group" style="grid-column:span 2"><label class="admin-form-label">Notes</label><textarea class="admin-form-control" name="description" id="pay-desc" rows="3"></textarea></div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="document.getElementById('payment-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════ EXPENSES TAB ══════ -->
<?php elseif ($tab === 'expenses'): ?>
<?php include __DIR__ . '/expenses.php'; ?>

<!-- ══════ REPORTS TAB ══════ -->
<?php elseif ($tab === 'reports'): ?>
<?php if (!empty($stats)): ?>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
    <div class="stat-card"><div class="stat-card-info"><small>Total Orders</small><strong><?php echo number_format((int)$stats['total_orders']); ?></strong></div><div class="stat-card-icon"><i data-lucide="shopping-bag" style="width:24px;height:24px;"></i></div></div>
    <div class="stat-card"><div class="stat-card-info"><small>Total Revenue</small><strong>KES <?php echo number_format((float)$stats['total_revenue'], 0); ?></strong></div><div class="stat-card-icon accent"><i data-lucide="trending-up" style="width:24px;height:24px;"></i></div></div>
    <div class="stat-card"><div class="stat-card-info"><small>Total Expenses</small><strong>KES <?php echo number_format((float)$stats['total_expenses'], 0); ?></strong></div><div class="stat-card-icon" style="background:#fee2e2;color:#b91c1c;"><i data-lucide="minus-circle" style="width:24px;height:24px;"></i></div></div>
    <div class="stat-card"><div class="stat-card-info"><small>Net Profit</small><strong>KES <?php echo number_format((float)$stats['total_revenue'] - (float)$stats['total_expenses'], 0); ?></strong></div><div class="stat-card-icon info"><i data-lucide="bar-chart-3" style="width:24px;height:24px;"></i></div></div>
</div>
<div class="admin-card">
    <h3 style="margin:0 0 18px;font-family:'Outfit',sans-serif;font-size:1.1rem;">Sales — Last 30 Days</h3>
    <?php if (!empty($stats['recent_sales'])): ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Date</th><th>Daily Revenue (KES)</th></tr></thead>
            <tbody>
            <?php foreach ($stats['recent_sales'] as $row): ?>
                <tr><td><?php echo htmlspecialchars($row['day'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo number_format((float)$row['total'], 2); ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p style="text-align:center;color:#94a3b8;padding:28px;">No sales data for the last 30 days.</p>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="admin-card" style="text-align:center;padding:40px;"><p style="color:#94a3b8;">Could not load report data.</p></div>
<?php endif; ?>

<?php endif; ?>

<script>
function openOrderModal(id, num, currentStatus) {
    document.getElementById('order-modal-id').value = id;
    document.getElementById('order-modal-label').textContent = 'Order #' + num;
    document.getElementById('order-modal-status').value = currentStatus;
    document.getElementById('order-modal').style.display = 'flex';
}
function openPaymentModal(data) {
    const modal = document.getElementById('payment-modal');
    const isEdit = data && data.id;
    document.getElementById('payment-modal-title').textContent = isEdit ? 'Edit Payment' : 'Add Payment';
    document.getElementById('pay-id').value   = isEdit ? data.id : '';
    document.getElementById('pay-cat').value  = data?.category || '';
    document.getElementById('pay-amt').value  = data?.amount || '';
    document.getElementById('pay-meth').value = data?.payment_method || 'Cash';
    document.getElementById('pay-stat').value = data?.payment_status || 'Pending';
    document.getElementById('pay-date').value = data?.transaction_date || '<?php echo date("Y-m-d"); ?>';
    document.getElementById('pay-desc').value = data?.description || '';
    modal.style.display = 'flex';
}
document.addEventListener('click', function(e) {
    ['order-modal','payment-modal'].forEach(id => {
        const el = document.getElementById(id);
        if (el && e.target === el) el.style.display = 'none';
    });
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
