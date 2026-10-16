<?php
/**
 * Admin — Sales Terminal & Point of Sale (Chickens & Feeds)
 * ERPNext-aligned unified terminal for:
 *   - Live Chickens & Meat (linked to Batches with bird count auto-deduction)
 *   - Finished Feeds (linked to Feed Stock with bag auto-deduction)
 *   - Eggs, Chicks & Farm Produce
 * Integrated with Cashbook & Customer Debtors Ledger.
 */
declare(strict_types=1);
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();
$page_title = 'Sales Terminal - Admin';
include __DIR__ . '/includes/admin_header.php';

$pdo = getDB();
$tab = $_GET['tab'] ?? 'sales';
$validTabs = ['sales','customers'];
if (!in_array($tab, $validTabs, true)) $tab = 'sales';

$customers = [];
$activeBatches = [];
$feedProducts = [];
$todaySalesKpis = ['total_amount' => 0, 'birds_sold' => 0, 'feed_sold' => 0, 'debtors' => 0];

if ($pdo) {
    $customers = safeQueryAll($pdo, "SELECT * FROM walk_in_customers ORDER BY customer_name");
    $activeBatches = safeQueryAll($pdo, "SELECT id, batch_name, batch_code, batch_type, current_birds FROM batches WHERE status='active' ORDER BY placement_date DESC");
    $feedProducts = safeQueryAll($pdo, "SELECT id, name, price, stock_quantity, weight_kg FROM products WHERE product_type='feed' AND is_active=1 ORDER BY name");
    
    // Quick KPIs
    $today = date('Y-m-d');
    $kpiRow = $pdo->query("SELECT 
        COALESCE(SUM(total_amount), 0) AS total_amount,
        COALESCE(SUM(CASE WHEN product_type IN ('broiler','kienyeji') THEN quantity ELSE 0 END), 0) AS birds_sold,
        COALESCE(SUM(CASE WHEN product_type = 'feed' THEN quantity ELSE 0 END), 0) AS feed_sold,
        COALESCE(SUM(balance), 0) AS debtors
        FROM bulk_sales WHERE sale_date = '$today'")->fetch(PDO::FETCH_ASSOC);
    if ($kpiRow) $todaySalesKpis = $kpiRow;
}
?>
<style>
@keyframes spin{to{transform:rotate(360deg)}}
.pos-stat-card {
    background: #fff;
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.pos-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}
.pos-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 9px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
}
.pos-quick-btn {
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #334155;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}
.pos-quick-btn:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
}
.pos-quick-btn.active {
    background: var(--admin-primary);
    border-color: var(--admin-primary);
    color: #fff;
}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;">Sales Terminal</h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:0.9rem;">Sell live chickens, dressed meat, finished feeds, and farm produce with automated stock & cashbook ledger.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <button class="btn btn-primary" onclick="openBulkSaleModal()"><i data-lucide="plus-circle" style="width:16px;height:16px;"></i> New Sale</button>
    </div>
</div>

<!-- Live Sales KPIs -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:14px;margin-bottom:22px;">
    <div class="pos-stat-card">
        <div>
            <small style="color:#64748b;font-size:0.78rem;font-weight:600;text-transform:uppercase;">Today's Revenue</small>
            <strong style="display:block;font-size:1.35rem;color:var(--admin-text-heading);margin-top:2px;">KES <?= number_format((float)$todaySalesKpis['total_amount'], 2) ?></strong>
        </div>
        <div style="width:42px;height:42px;border-radius:10px;background:#eef7f0;color:#15803d;display:flex;align-items:center;justify-content:center;">
            <i data-lucide="banknote" style="width:20px;height:20px;"></i>
        </div>
    </div>
    <div class="pos-stat-card">
        <div>
            <small style="color:#64748b;font-size:0.78rem;font-weight:600;text-transform:uppercase;">Chickens Sold Today</small>
            <strong style="display:block;font-size:1.35rem;color:var(--admin-text-heading);margin-top:2px;"><?= number_format((float)$todaySalesKpis['birds_sold']) ?> birds</strong>
        </div>
        <div style="width:42px;height:42px;border-radius:10px;background:#fef3c7;color:#b45309;display:flex;align-items:center;justify-content:center;">
            <i data-lucide="bird" style="width:20px;height:20px;"></i>
        </div>
    </div>
    <div class="pos-stat-card">
        <div>
            <small style="color:#64748b;font-size:0.78rem;font-weight:600;text-transform:uppercase;">Feed Bags Sold Today</small>
            <strong style="display:block;font-size:1.35rem;color:var(--admin-text-heading);margin-top:2px;"><?= number_format((float)$todaySalesKpis['feed_sold']) ?> bags</strong>
        </div>
        <div style="width:42px;height:42px;border-radius:10px;background:#ede9fe;color:#6d28d9;display:flex;align-items:center;justify-content:center;">
            <i data-lucide="package" style="width:20px;height:20px;"></i>
        </div>
    </div>
    <div class="pos-stat-card">
        <div>
            <small style="color:#64748b;font-size:0.78rem;font-weight:600;text-transform:uppercase;">Unpaid Balance (Credit)</small>
            <strong style="display:block;font-size:1.35rem;color:<?= (float)$todaySalesKpis['debtors'] > 0 ? '#b91c1c' : '#15803d' ?>;margin-top:2px;">KES <?= number_format((float)$todaySalesKpis['debtors'], 2) ?></strong>
        </div>
        <div style="width:42px;height:42px;border-radius:10px;background:<?= (float)$todaySalesKpis['debtors'] > 0 ? '#fee2e2' : '#f0fdf4' ?>;color:<?= (float)$todaySalesKpis['debtors'] > 0 ? '#b91c1c' : '#166534' ?>;display:flex;align-items:center;justify-content:center;">
            <i data-lucide="hand-coins" style="width:20px;height:20px;"></i>
        </div>
    </div>
</div>

<!-- Tab Bar -->
<div style="display:flex;gap:4px;background:#f1f5f9;padding:5px;border-radius:10px;margin-bottom:24px;overflow-x:auto;">
    <a href="?tab=sales" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;<?= $tab==='sales'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>"><i data-lucide="receipt" style="width:15px;height:15px;"></i> Sales Register</a>
    <a href="?tab=customers" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;<?= $tab==='customers'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>"><i data-lucide="users" style="width:15px;height:15px;"></i> Customer Directory</a>
</div>

<?php if ($tab === 'sales'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Recent Transactions</h3>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <input class="admin-form-control" type="date" id="bs-from" onchange="loadBulkSales()" style="max-width:150px;">
            <input class="admin-form-control" type="date" id="bs-to" onchange="loadBulkSales()" style="max-width:150px;">
            <a href="/Backend/api/export.php?module=bulk_sales" class="btn btn-outline" id="bs-export"><i data-lucide="download" style="width:14px;height:14px;"></i> Export</a>
            <button class="btn btn-primary" onclick="openBulkSaleModal()"><i data-lucide="shopping-cart" style="width:15px;height:15px;"></i> Record Sale</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr>
                <th>Date</th>
                <th>Receipt #</th>
                <th>Customer</th>
                <th>Item / Origin</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Actions</th>
            </tr></thead>
            <tbody id="bs-body">
                <tr><td colspan="11" style="text-align:center;padding:28px;color:#94a3b8;">Loading sales...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php else: /* customers */ ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Customer Directory</h3>
        <div style="display:flex;gap:8px;">
            <a href="/Backend/api/export.php?module=customers" class="btn btn-outline"><i data-lucide="download" style="width:14px;height:14px;"></i> Export</a>
            <button class="btn btn-primary" onclick="openCustomerModal()"><i data-lucide="plus" style="width:15px;height:15px;"></i> Add Customer</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Phone</th><th>Category</th><th>Address</th><th>Registered</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($customers)): ?>
                <tr><td colspan="6" style="text-align:center;padding:28px;color:#94a3b8;">No customers registered yet.</td></tr>
            <?php else: foreach ($customers as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['customer_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><?= htmlspecialchars($c['phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge-pill badge-pill-info"><?= htmlspecialchars(ucfirst($c['customer_type'] ?? 'retail'), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= htmlspecialchars($c['address'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(substr($c['created_at'] ?? '', 0, 10), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <button class="btn btn-trans btn-sm" onclick='openCustomerModal(<?= json_encode($c) ?>)'><i data-lucide="pencil" style="width:13px;height:13px;"></i></button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Unified Sales Modal (Chickens & Feeds) -->
<div id="bs-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:2000;align-items:center;justify-content:center;overflow-y:auto;padding:16px;backdrop-filter:blur(3px);">
    <div style="background:#fff;padding:28px;border-radius:14px;width:100%;max-width:680px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);max-height:92vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:14px;">
            <div>
                <h3 id="bs-modal-title" style="margin:0;font-family:'Outfit',sans-serif;font-size:1.25rem;font-weight:700;">Record Farm Sale</h3>
                <small style="color:#64748b;">Chickens auto-deduct from Batch; Feeds auto-deduct from Products inventory.</small>
            </div>
            <button type="button" onclick="closeBulkSaleModal()" style="border:none;background:transparent;cursor:pointer;color:#94a3b8;"><i data-lucide="x" style="width:20px;height:20px;"></i></button>
        </div>

        <form id="bs-form">
            <input type="hidden" id="bs-id">
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Date *</label>
                    <input class="admin-form-control" type="date" id="bs-date" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Customer Profile</label>
                    <select class="admin-form-control" id="bs-customer" onchange="fillCustomer()">
                        <option value="">— Walk-in Customer (Quick Sale) —</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" data-name="<?= htmlspecialchars($c['customer_name'], ENT_QUOTES, 'UTF-8') ?>" data-phone="<?= htmlspecialchars($c['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($c['customer_name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($c['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Customer Name *</label>
                    <input class="admin-form-control" id="bs-name" required placeholder="e.g. John Omondi">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Phone Number</label>
                    <input class="admin-form-control" id="bs-phone" placeholder="07XX XXX XXX">
                </div>
            </div>

            <!-- Product Category Selector -->
            <div style="background:#f8fafc;padding:14px;border-radius:10px;margin:12px 0;border:1px solid #e2e8f0;">
                <div class="admin-form-group" style="margin-bottom:10px;">
                    <label class="admin-form-label" style="font-weight:700;">What are you selling? *</label>
                    <select class="admin-form-control" id="bs-product" required onchange="handleProductTypeChange()" style="font-weight:600;">
                        <option value="broiler">Broiler Chickens (Live / Meat)</option>
                        <option value="kienyeji">Kienyeji Chickens (Live / Meat)</option>
                        <option value="feed">Finished Feeds (Bags)</option>
                        <option value="eggs">Eggs (Crates)</option>
                        <option value="chicks">Day-Old Chicks</option>
                        <option value="manure">Organic Manure (Bags)</option>
                        <option value="other">Other Farm Items</option>
                    </select>
                </div>

                <!-- Chicken Batch Row (Shown when selling chickens) -->
                <div id="chicken-batch-row" style="display:block;margin-top:10px;">
                    <label class="admin-form-label" style="color:var(--admin-primary);font-weight:700;">Select Flock / Batch *</label>
                    <select class="admin-form-control" id="bs-batch" onchange="handleBatchChange()">
                        <option value="">— Select Chicken Batch to Deduct Birds —</option>
                        <?php foreach ($activeBatches as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" data-type="<?= $b['batch_type'] ?>" data-birds="<?= (int)$b['current_birds'] ?>">
                                <?= htmlspecialchars($b['batch_name'], ENT_QUOTES, 'UTF-8') ?> (<?= $b['batch_code'] ?>) — <?= (int)$b['current_birds'] ?> live birds remaining
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small id="batch-birds-avail" style="color:#64748b;display:block;margin-top:4px;">Live birds will be automatically deducted from this batch.</small>
                </div>

                <!-- Feed Product Row (Shown when selling feed) -->
                <div id="feed-product-row" style="display:none;margin-top:10px;">
                    <label class="admin-form-label" style="color:var(--admin-primary);font-weight:700;">Select Finished Feed *</label>
                    <select class="admin-form-control" id="bs-feed-product" onchange="handleFeedProductChange()">
                        <option value="">— Select Feed Item in Stock —</option>
                        <?php foreach ($feedProducts as $fp): ?>
                            <option value="<?= (int)$fp['id'] ?>" data-price="<?= (float)$fp['price'] ?>" data-stock="<?= (int)$fp['stock_quantity'] ?>" data-weight="<?= (float)$fp['weight_kg'] ?>">
                                <?= htmlspecialchars($fp['name'], ENT_QUOTES, 'UTF-8') ?> — <?= (int)$fp['stock_quantity'] ?> bags in stock (KES <?= number_format((float)$fp['price'], 2) ?>/bag)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Pricing & Quantity Grid -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Quantity *</label>
                    <input class="admin-form-control" type="number" step="0.01" id="bs-qty" required min="0.01" placeholder="0" oninput="recalcSale()">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Unit</label>
                    <select class="admin-form-control" id="bs-unit" onchange="recalcSale()">
                        <option value="piece">Piece (Bird)</option>
                        <option value="kg">Kilogram (kg)</option>
                        <option value="bag">Bag</option>
                        <option value="crate">Crate</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Unit Price (KES) *</label>
                    <input class="admin-form-control" type="number" step="0.01" id="bs-price" required min="0.01" placeholder="0.00" oninput="recalcSale()">
                </div>
            </div>

            <!-- Total Calculation Display -->
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin:12px 0;">
                <div>
                    <span style="color:#166534;font-size:0.85rem;font-weight:600;">Total Sale Amount</span>
                    <strong id="sale-total-text" style="display:block;font-size:1.35rem;color:#15803d;font-family:'Outfit',sans-serif;">KES 0.00</strong>
                </div>
                <div style="text-align:right;">
                    <span style="color:#64748b;font-size:0.85rem;">Balance Due:</span>
                    <strong id="sale-balance-text" style="display:block;font-size:1.2rem;color:#0f172a;">KES 0.00</strong>
                </div>
            </div>

            <!-- Payment Row -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
                <div class="admin-form-group">
                    <label class="admin-form-label">Amount Paid (KES) *</label>
                    <input class="admin-form-control" type="number" step="0.01" id="bs-paid" value="0" oninput="recalcSale()">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Payment Method</label>
                    <select class="admin-form-control" id="bs-method">
                        <option value="cash">Cash</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="credit">Credit (Pay Later)</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Payment Status</label>
                    <select class="admin-form-control" id="bs-status">
                        <option value="paid">Paid in Full</option>
                        <option value="partial">Partial Payment</option>
                        <option value="pending">Pending / Credit</option>
                    </select>
                </div>
            </div>

            <!-- Quick presets -->
            <div style="display:flex;gap:8px;align-items:center;margin-bottom:14px;flex-wrap:wrap;">
                <span style="font-size:0.75rem;color:#64748b;font-weight:600;">Quick Actions:</span>
                <button type="button" class="pos-quick-btn" onclick="setFullPayment('cash')">Full Cash</button>
                <button type="button" class="pos-quick-btn" onclick="setFullPayment('mpesa')">Full M-Pesa</button>
                <button type="button" class="pos-quick-btn" onclick="setCreditPayment()">Credit / Pay Later</button>
            </div>

            <div class="admin-form-group"><label class="admin-form-label">Notes / Reference</label><textarea class="admin-form-control" id="bs-notes" rows="2" placeholder="e.g. Weighbridge slip #124, 5 live birds slaughtered"></textarea></div>

            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeBulkSaleModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="check-circle" style="width:16px;height:16px;"></i> Finalize Sale</button>
            </div>
        </form>
    </div>
</div>

<!-- Customer Modal -->
<div id="cust-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:2000;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(3px);">
    <div style="background:#fff;padding:28px;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 40px rgba(0,0,0,0.15);">
        <h3 id="cust-modal-title" style="margin:0 0 18px;font-family:'Outfit',sans-serif;">Add Customer</h3>
        <form id="cust-form">
            <input type="hidden" id="c-id">
            <div class="admin-form-group"><label class="admin-form-label">Customer Name *</label><input class="admin-form-control" id="c-name" required placeholder="Full Name or Business"></div>
            <div class="admin-form-group"><label class="admin-form-label">Phone Number</label><input class="admin-form-control" id="c-phone" placeholder="07XX XXX XXX"></div>
            <div class="admin-form-group"><label class="admin-form-label">Customer Type</label>
                <select class="admin-form-control" id="c-type">
                    <option value="retail">Retail Buyer</option>
                    <option value="wholesale">Wholesale / Merchant</option>
                    <option value="institution">Hotel / Institution</option>
                    <option value="agent">Sales Agent</option>
                </select>
            </div>
            <div class="admin-form-group"><label class="admin-form-label">Town / Location</label><input class="admin-form-control" id="c-addr" placeholder="e.g. Busia Town"></div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeCustomerModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Customer</button>
            </div>
        </form>
    </div>
</div>

<script>
let allBatches = <?= json_encode($activeBatches) ?>;
let allFeedProducts = <?= json_encode($feedProducts) ?>;

function handleProductTypeChange() {
    const type = document.getElementById('bs-product').value;
    const batchRow = document.getElementById('chicken-batch-row');
    const feedRow = document.getElementById('feed-product-row');
    const unitSel = document.getElementById('bs-unit');

    if (type === 'broiler' || type === 'kienyeji' || type === 'chicks') {
        batchRow.style.display = 'block';
        feedRow.style.display = 'none';
        unitSel.value = 'piece';
    } else if (type === 'feed') {
        batchRow.style.display = 'none';
        feedRow.style.display = 'block';
        unitSel.value = 'bag';
    } else if (type === 'eggs') {
        batchRow.style.display = 'none';
        feedRow.style.display = 'none';
        unitSel.value = 'crate';
    } else {
        batchRow.style.display = 'none';
        feedRow.style.display = 'none';
    }
    recalcSale();
}

function handleBatchChange() {
    const sel = document.getElementById('bs-batch');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        const birds = opt.dataset.birds || 0;
        document.getElementById('batch-birds-avail').textContent = `Live birds available in this batch: ${birds} birds.`;
    }
}

function handleFeedProductChange() {
    const sel = document.getElementById('bs-feed-product');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        const price = opt.dataset.price || 0;
        document.getElementById('bs-price').value = price;
        document.getElementById('bs-unit').value = 'bag';
        recalcSale();
    }
}

function recalcSale() {
    const qty = parseFloat(document.getElementById('bs-qty').value || 0);
    const price = parseFloat(document.getElementById('bs-price').value || 0);
    const total = qty * price;
    let paid = parseFloat(document.getElementById('bs-paid').value || 0);
    
    const balance = Math.max(0, total - paid);
    document.getElementById('sale-total-text').textContent = 'KES ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('sale-balance-text').textContent = 'KES ' + balance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Auto status
    const statusSel = document.getElementById('bs-status');
    if (paid >= total && total > 0) statusSel.value = 'paid';
    else if (paid > 0 && paid < total) statusSel.value = 'partial';
    else statusSel.value = 'pending';
}

function setFullPayment(method) {
    const qty = parseFloat(document.getElementById('bs-qty').value || 0);
    const price = parseFloat(document.getElementById('bs-price').value || 0);
    const total = qty * price;
    document.getElementById('bs-paid').value = total;
    document.getElementById('bs-method').value = method;
    document.getElementById('bs-status').value = 'paid';
    recalcSale();
}

function setCreditPayment() {
    document.getElementById('bs-paid').value = 0;
    document.getElementById('bs-method').value = 'credit';
    document.getElementById('bs-status').value = 'pending';
    recalcSale();
}

function fillCustomer() {
    const sel = document.getElementById('bs-customer');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('bs-name').value = opt.dataset.name || '';
        document.getElementById('bs-phone').value = opt.dataset.phone || '';
    }
}

function openBulkSaleModal(d = null) {
    document.getElementById('bs-modal-title').textContent = d?.id ? 'Edit Sale' : 'Record Farm Sale';
    document.getElementById('bs-id').value = d?.id || '';
    document.getElementById('bs-date').value = d?.sale_date || new Date().toISOString().split('T')[0];
    document.getElementById('bs-customer').value = d?.customer_id || '';
    document.getElementById('bs-name').value = d?.customer_name || '';
    document.getElementById('bs-phone').value = d?.customer_phone || '';
    document.getElementById('bs-product').value = d?.product_type || 'broiler';
    document.getElementById('bs-batch').value = d?.batch_id || '';
    document.getElementById('bs-feed-product').value = d?.product_id || '';
    document.getElementById('bs-qty').value = d?.quantity || '';
    document.getElementById('bs-unit').value = d?.unit || 'piece';
    document.getElementById('bs-price').value = d?.unit_price || '';
    document.getElementById('bs-paid').value = d?.amount_paid || 0;
    document.getElementById('bs-method').value = d?.payment_method || 'cash';
    document.getElementById('bs-status').value = d?.payment_status || 'paid';
    document.getElementById('bs-notes').value = d?.notes || '';
    
    handleProductTypeChange();
    recalcSale();
    document.getElementById('bs-modal').style.display = 'flex';
}
function closeBulkSaleModal() { document.getElementById('bs-modal').style.display = 'none'; }

document.getElementById('bs-form').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('id', document.getElementById('bs-id').value);
    fd.append('sale_date', document.getElementById('bs-date').value);
    fd.append('customer_id', document.getElementById('bs-customer').value);
    fd.append('customer_name', document.getElementById('bs-name').value);
    fd.append('customer_phone', document.getElementById('bs-phone').value);
    fd.append('batch_id', document.getElementById('bs-batch').value);
    fd.append('product_id', document.getElementById('bs-feed-product').value);
    fd.append('product_type', document.getElementById('bs-product').value);
    fd.append('quantity', document.getElementById('bs-qty').value);
    fd.append('unit', document.getElementById('bs-unit').value);
    fd.append('unit_price', document.getElementById('bs-price').value);
    fd.append('amount_paid', document.getElementById('bs-paid').value);
    fd.append('payment_method', document.getElementById('bs-method').value);
    fd.append('payment_status', document.getElementById('bs-status').value);
    fd.append('notes', document.getElementById('bs-notes').value);

    const res = await fetch('/Backend/api/admin_poultry_v2.php?action=save_bulk_sale', {method:'POST', body:fd});
    const r = await res.json();
    if (r.success) {
        alert(r.message || 'Sale recorded successfully');
        closeBulkSaleModal();
        loadBulkSales();
    } else {
        alert('Error: ' + r.message);
    }
});

async function loadBulkSales() {
    const tbody = document.getElementById('bs-body');
    if (!tbody) return;
    const from = document.getElementById('bs-from')?.value || '';
    const to = document.getElementById('bs-to')?.value || '';
    let url = '/Backend/api/admin_poultry_v2.php?action=get_bulk_sales';
    if (from) url += '&from=' + from;
    if (to) url += '&to=' + to;
    tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;padding:28px;color:#94a3b8;">Loading sales...</td></tr>';
    try {
        const res = await fetch(url);
        const r = await res.json();
        if (!r.success) { tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;color:#dc2626;">' + (r.message||'Failed') + '</td></tr>'; return; }
        const data = r.data || [];
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;padding:28px;color:#94a3b8;">No sales records found.</td></tr>'; return; }
        tbody.innerHTML = data.map(s => {
            const statusBadge = s.payment_status==='paid'?'badge-pill-success':(s.payment_status==='partial'?'badge-pill-warning':'badge-pill-info');
            const originText = s.batch_code ? `<span class="badge-pill badge-pill-info">${escapeHtml(s.batch_code)}</span>` : (s.product_name ? `<span style="font-size:0.8rem;color:#64748b;">${escapeHtml(s.product_name)}</span>` : '—');
            return `<tr>
                <td>${s.sale_date}</td>
                <td><code style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-weight:700;">${s.sale_number}</code></td>
                <td><strong>${escapeHtml(s.customer_name)}</strong>${s.customer_phone?'<br><small style="color:#64748b;">'+escapeHtml(s.customer_phone)+'</small>':''}</td>
                <td><strong>${escapeHtml(s.product_type)}</strong><br>${originText}</td>
                <td><strong>${parseFloat(s.quantity).toFixed(2)}</strong> ${escapeHtml(s.unit)}</td>
                <td>KES ${parseFloat(s.unit_price).toFixed(2)}</td>
                <td><strong style="color:var(--admin-primary);">KES ${parseFloat(s.total_amount).toFixed(2)}</strong></td>
                <td>KES ${parseFloat(s.amount_paid).toFixed(2)}</td>
                <td style="font-weight:700;color:${parseFloat(s.balance)>0?'#dc2626':'#16a34a'};">KES ${parseFloat(s.balance).toFixed(2)}</td>
                <td><span class="badge-pill ${statusBadge}">${escapeHtml(s.payment_status)}</span><br><small style="color:#64748b;text-transform:capitalize;">${escapeHtml(s.payment_method)}</small></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button class="btn btn-trans btn-sm" onclick='printSlip(${JSON.stringify(s)})' title="Print Slip"><i data-lucide="printer" style="width:13px;height:13px;"></i></button>
                        <button class="btn btn-trans btn-sm" onclick='openBulkSaleModal(${JSON.stringify(s)})' title="Edit"><i data-lucide="pencil" style="width:13px;height:13px;"></i></button>
                        <button class="btn btn-danger btn-sm" onclick="deleteBulkSale(${s.id})" title="Delete"><i data-lucide="trash-2" style="width:13px;height:13px;"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;color:#dc2626;">Network error loading sales.</td></tr>';
    }
}

function printSlip(s) {
    const w = window.open('', '_blank', 'width=600,height=700');
    if (!w) { alert('Popup blocked. Please allow popups.'); return; }
    w.document.write(`
        <!DOCTYPE html><html><head><title>Sales Receipt #${s.sale_number}</title>
        <style>
            body{font-family:'Courier New',Courier,monospace;padding:24px;color:#111;max-width:440px;margin:0 auto;}
            .header{text-align:center;border-bottom:2px dashed #000;padding-bottom:12px;margin-bottom:16px;}
            .title{font-size:18px;font-weight:bold;margin:0;}
            .line{display:flex;justify-content:space-between;margin:6px 0;font-size:14px;}
            .total{border-top:1px dashed #000;border-bottom:1px dashed #000;padding:8px 0;margin:12px 0;font-weight:bold;}
            .footer{text-align:center;margin-top:20px;font-size:12px;color:#555;}
        </style></head><body>
            <div class="header">
                <p class="title">BUSIA CHICKEN & FEEDS</p>
                <p style="margin:4px 0;">Official Sales Receipt</p>
                <small>${s.sale_date} | #${s.sale_number}</small>
            </div>
            <div class="line"><span>Customer:</span><strong>${escapeHtml(s.customer_name)}</strong></div>
            <div class="line"><span>Phone:</span><span>${escapeHtml(s.customer_phone || '—')}</span></div>
            <div class="line"><span>Product:</span><strong>${escapeHtml(s.product_type)} (${escapeHtml(s.unit)})</strong></div>
            ${s.batch_code ? '<div class="line"><span>Batch:</span><span>'+escapeHtml(s.batch_code)+'</span></div>' : ''}
            <div class="line"><span>Quantity:</span><span>${parseFloat(s.quantity).toFixed(2)} ${escapeHtml(s.unit)}</span></div>
            <div class="line"><span>Price/Unit:</span><span>KES ${parseFloat(s.unit_price).toFixed(2)}</span></div>
            <div class="total line"><span>TOTAL AMOUNT:</span><span>KES ${parseFloat(s.total_amount).toFixed(2)}</span></div>
            <div class="line"><span>Amount Paid (${s.payment_method}):</span><span>KES ${parseFloat(s.amount_paid).toFixed(2)}</span></div>
            <div class="line"><span>Balance Due:</span><span>KES ${parseFloat(s.balance).toFixed(2)}</span></div>
            <div class="footer">
                <p>Thank you for choosing Busia Chicken & Feeds!</p>
                <small>Goods once sold cannot be returned.</small>
            </div>
            <script>window.print();<\/script>
        </body></html>
    `);
    w.document.close();
}

async function deleteBulkSale(id) {
    if (!confirm('Are you sure you want to delete this sale record?')) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('/Backend/api/admin_poultry_v2.php?action=delete_bulk_sale', {method:'POST', body:fd});
    const r = await res.json();
    if (r.success) loadBulkSales(); else alert(r.message);
}

function openCustomerModal(d = null) {
    document.getElementById('cust-modal-title').textContent = d?.id ? 'Edit Customer' : 'Add Customer';
    document.getElementById('c-id').value = d?.id || '';
    document.getElementById('c-name').value = d?.customer_name || '';
    document.getElementById('c-phone').value = d?.phone || '';
    document.getElementById('c-type').value = d?.customer_type || 'retail';
    document.getElementById('c-addr').value = d?.address || '';
    document.getElementById('cust-modal').style.display = 'flex';
}
function closeCustomerModal() { document.getElementById('cust-modal').style.display = 'none'; }

document.getElementById('cust-form').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('id', document.getElementById('c-id').value);
    fd.append('customer_name', document.getElementById('c-name').value);
    fd.append('phone', document.getElementById('c-phone').value);
    fd.append('customer_type', document.getElementById('c-type').value);
    fd.append('address', document.getElementById('c-addr').value);
    const res = await fetch('/Backend/api/admin_poultry_v2.php?action=save_walkin_customer', {method:'POST', body:fd});
    const r = await res.json();
    if (r.success) {
        alert('Customer profile saved');
        closeCustomerModal();
        location.reload();
    } else {
        alert('Error: ' + r.message);
    }
});

function escapeHtml(s){ if(s==null) return ''; return String(s).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }

document.addEventListener('DOMContentLoaded', () => {
    loadBulkSales();
    if (typeof lucide !== 'undefined') lucide.createIcons();
    const exp = document.getElementById('bs-export');
    if (exp) {
        const update = () => {
            const f = document.getElementById('bs-from')?.value || '';
            const t = document.getElementById('bs-to')?.value || '';
            exp.href = '/Backend/api/export.php?module=bulk_sales' + (f?'&from='+f:'') + (t?'&to='+t:'');
        };
        document.getElementById('bs-from')?.addEventListener('change', update);
        document.getElementById('bs-to')?.addEventListener('change', update);
    }
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php';
