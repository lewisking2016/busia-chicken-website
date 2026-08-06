<?php
/**
 * Hub: Inventory & Store — Products, Farm Equipment, Feed Stock, Stock Alerts
 */
declare(strict_types=1);
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();
$page_title = 'Inventory & Store - Admin';
include __DIR__ . '/includes/admin_header.php';

$tab = $_GET['tab'] ?? 'products';
$validTabs = ['products','equipment','feedstock','alerts'];
if (!in_array($tab, $validTabs, true)) $tab = 'products';

$pdo = getDB();
$message = ''; $error_message = '';

/* ── Handle POST ─────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $postAction = $_POST['_action'] ?? '';

    /* Save farm equipment item */
    if ($postAction === 'save_equipment') {
        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $qty      = (int)($_POST['quantity'] ?? 1);
        $cond     = trim($_POST['condition_status'] ?? 'Good');
        $pur_date = trim($_POST['purchase_date'] ?? '');
        $cost     = (float)($_POST['cost'] ?? 0);
        $notes    = trim($_POST['notes'] ?? '');

        if ($name === '') {
            $error_message = 'Item name is required.';
        } else {
            try {
                if ($id > 0) {
                    $pdo->prepare('UPDATE farm_items SET name=?,category=?,quantity=?,condition_status=?,purchase_date=?,cost=?,notes=? WHERE id=?')
                        ->execute([$name,$category,$qty,$cond,$pur_date?:null,$cost?:null,$notes,$id]);
                    $message = 'Equipment updated successfully.';
                } else {
                    $pdo->prepare('INSERT INTO farm_items (name,category,quantity,condition_status,purchase_date,cost,notes) VALUES (?,?,?,?,?,?,?)')
                        ->execute([$name,$category,$qty,$cond,$pur_date?:null,$cost?:null,$notes]);
                    $message = 'Equipment added successfully.';
                }
            } catch (Exception $e) { $error_message = $e->getMessage(); }
        }
        $tab = 'equipment';
    }
}

/* ── Load tab data ─────────────────────────────────── */
$farmItems = [];
if ($pdo) {
    try {
        if ($tab === 'equipment') {
            $farmItems = $pdo->query('SELECT * FROM farm_items ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) { $error_message = $e->getMessage(); }
}

$tabs = [
    'products'  => ['icon' => 'package',       'label' => 'Products'],
    'equipment' => ['icon' => 'wrench',         'label' => 'Farm Equipment'],
    'feedstock' => ['icon' => 'layers',         'label' => 'Feed & Stock'],
    'alerts'    => ['icon' => 'bell',           'label' => 'Stock Alerts'],
];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;">Inventory & Store</h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:0.9rem;">Manage your online store products, farm equipment, feed stock, and inventory alerts.</p>
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
       style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;white-space:nowrap;font-weight:600;font-size:0.86rem;transition:all 0.2s;
              <?php echo $tab === $key ? 'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);' : 'color:#64748b;'; ?>">
        <i data-lucide="<?php echo $info['icon']; ?>" style="width:15px;height:15px;"></i>
        <?php echo $info['label']; ?>
    </a>
<?php endforeach; ?>
</div>

<!-- ══════ PRODUCTS ══════ -->
<?php if ($tab === 'products'): ?>
<?php include __DIR__ . '/products.php'; ?>

<!-- ══════ EQUIPMENT ══════ -->
<?php elseif ($tab === 'equipment'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Farm Equipment & Tools</h3>
            <p style="margin:4px 0 0;font-size:0.85rem;color:#64748b;">Track all physical farm items, tools, machinery, and their condition.</p>
        </div>
        <button class="btn btn-primary" onclick="openEquipModal()">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Add Item
        </button>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Category</th><th>Qty</th><th>Condition</th><th>Purchase Date</th><th>Cost (KES)</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($farmItems)): ?>
                <tr><td colspan="7" style="text-align:center;padding:28px;color:#94a3b8;">No equipment registered yet. Click "Add Item" to start.</td></tr>
            <?php else: foreach ($farmItems as $fi): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($fi['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><?php echo htmlspecialchars($fi['category'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int)($fi['quantity'] ?? 1); ?></td>
                    <td>
                        <?php
                        $condClass = ['Good'=>'badge-pill-success','Fair'=>'badge-pill-warning','Poor'=>'badge-pill-danger','New'=>'badge-pill-success'];
                        $cond = $fi['condition_status'] ?? 'Good';
                        ?>
                        <span class="badge-pill <?php echo $condClass[$cond] ?? 'badge-pill-warning'; ?>"><?php echo htmlspecialchars($cond, ENT_QUOTES, 'UTF-8'); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($fi['purchase_date'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $fi['cost'] ? number_format((float)$fi['cost'], 2) : '-'; ?></td>
                    <td>
                        <div class="tbl-actions">
                            <button class="btn btn-trans btn-sm" onclick='openEquipModal(<?php echo htmlspecialchars(json_encode($fi), ENT_QUOTES, "UTF-8"); ?>)'>
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

<!-- Equipment Modal -->
<div id="equip-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;width:100%;max-width:540px;box-shadow:0 20px 40px rgba(0,0,0,0.15);max-height:90vh;overflow-y:auto;">
        <h3 id="equip-modal-title" style="margin:0 0 22px;font-family:'Outfit',sans-serif;">Add Equipment / Tool</h3>
        <form method="POST">
            <input type="hidden" name="_action" value="save_equipment">
            <input type="hidden" name="id" id="equip-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="admin-form-group" style="grid-column:span 2"><label class="admin-form-label">Item Name *</label><input class="admin-form-control" name="name" id="equip-name" required placeholder="e.g. Water Pump, Feed Mixer…"></div>
                <div class="admin-form-group"><label class="admin-form-label">Category</label>
                    <select class="admin-form-control" name="category" id="equip-cat">
                        <?php foreach (['Machinery','Tools','Feeding Equipment','Watering Equipment','Transport','Storage','PPE','Other'] as $ec): ?>
                        <option><?php echo $ec; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group"><label class="admin-form-label">Quantity</label><input class="admin-form-control" type="number" name="quantity" id="equip-qty" min="1" value="1"></div>
                <div class="admin-form-group"><label class="admin-form-label">Condition</label>
                    <select class="admin-form-control" name="condition_status" id="equip-cond">
                        <option>New</option><option>Good</option><option>Fair</option><option>Poor</option>
                    </select>
                </div>
                <div class="admin-form-group"><label class="admin-form-label">Purchase Date</label><input class="admin-form-control" type="date" name="purchase_date" id="equip-date"></div>
                <div class="admin-form-group"><label class="admin-form-label">Cost (KES)</label><input class="admin-form-control" type="number" step="0.01" name="cost" id="equip-cost" placeholder="0.00"></div>
                <div class="admin-form-group" style="grid-column:span 2"><label class="admin-form-label">Notes</label><textarea class="admin-form-control" name="notes" id="equip-notes" rows="3" placeholder="Location, serial number, responsible person…"></textarea></div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeEquipModal()"><i data-lucide="x" style="width:15px;height:15px;"></i> Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Item</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════ FEED & STOCK ══════ -->
<?php elseif ($tab === 'feedstock'): ?>
<?php include __DIR__ . '/stock_dashboard.php'; ?>

<!-- ══════ ALERTS ══════ -->
<?php elseif ($tab === 'alerts'): ?>
<?php include __DIR__ . '/stock_alerts.php'; ?>

<?php endif; ?>

<script>
function openEquipModal(data) {
    const modal = document.getElementById('equip-modal');
    const isEdit = data && data.id;
    document.getElementById('equip-modal-title').textContent = isEdit ? 'Edit Equipment / Tool' : 'Add Equipment / Tool';
    document.getElementById('equip-id').value    = isEdit ? data.id : '';
    document.getElementById('equip-name').value  = data?.name || '';
    document.getElementById('equip-cat').value   = data?.category || 'Tools';
    document.getElementById('equip-qty').value   = data?.quantity || 1;
    document.getElementById('equip-cond').value  = data?.condition_status || 'Good';
    document.getElementById('equip-date').value  = data?.purchase_date || '';
    document.getElementById('equip-cost').value  = data?.cost || '';
    document.getElementById('equip-notes').value = data?.notes || '';
    modal.style.display = 'flex';
}
function closeEquipModal() { document.getElementById('equip-modal').style.display = 'none'; }
document.addEventListener('click', function(e) {
    const modal = document.getElementById('equip-modal');
    if (modal && e.target === modal) modal.style.display = 'none';
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
