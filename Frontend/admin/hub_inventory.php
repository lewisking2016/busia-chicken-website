<?php
/**
 * Hub: Inventory & Warehouse Center — Poultry & Feed Edition
 * High-level control dashboard for:
 *   - Finished Feeds & Product Catalog
 *   - Raw Materials & Grain Store
 *   - Batch Live Birds Stock
 */
declare(strict_types=1);
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager','stock_manager','sales_staff'], true)) {
    echo "<script>window.location.href='/busiaadmin';</script>"; exit;
}

$page_title = 'Inventory & Warehouse - Admin';
include __DIR__ . '/includes/admin_header.php';

$pdo = getDB();
$tab = $_GET['tab'] ?? 'overview';
$validTabs = ['overview', 'products', 'stores', 'feeds'];
if (!in_array($tab, $validTabs, true)) $tab = 'overview';

$feedBagsCount = 0;
$feedStockVal = 0.0;
$rawMaterialsKg = 0.0;
$rawMaterialsVal = 0.0;
$liveBirdsCount = 0;
$activeBatchesCount = 0;
$lowStockItems = [];

if ($pdo) {
    // Finished feeds
    $feedRow = $pdo->query("SELECT COALESCE(SUM(stock_quantity), 0) AS bags, COALESCE(SUM(stock_quantity * price), 0) AS val FROM products WHERE product_type='feed' AND is_active=1")->fetch(PDO::FETCH_ASSOC);
    if ($feedRow) {
        $feedBagsCount = (int)$feedRow['bags'];
        $feedStockVal = (float)$feedRow['val'];
    }

    // Raw materials
    $rawRow = $pdo->query("SELECT COALESCE(SUM(current_stock), 0) AS kg, COALESCE(SUM(current_stock * current_price_per_unit), 0) AS val FROM raw_materials WHERE category='feed_ingredient'")->fetch(PDO::FETCH_ASSOC);
    if ($rawRow) {
        $rawMaterialsKg = (float)$rawRow['kg'];
        $rawMaterialsVal = (float)$rawRow['val'];
    }

    // Live birds
    $birdRow = $pdo->query("SELECT COUNT(*) AS batches, COALESCE(SUM(current_birds), 0) AS birds FROM batches WHERE status='active'")->fetch(PDO::FETCH_ASSOC);
    if ($birdRow) {
        $activeBatchesCount = (int)$birdRow['batches'];
        $liveBirdsCount = (int)$birdRow['birds'];
    }

    // Low stock items
    $lowStockItems = safeQueryAll($pdo, "SELECT material_name, current_stock, min_stock_level, unit FROM raw_materials WHERE current_stock <= min_stock_level AND category='feed_ingredient' LIMIT 8");
}
?>
<style>
@keyframes spin{to{transform:rotate(360deg)}}
.hub-card {
    background: #fff;
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.hub-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;">Inventory & Warehouse</h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:0.9rem;">Complete overview of finished feed bags, raw grain inventory, and live flock assets.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/Frontend/admin/stores.php" class="btn btn-outline"><i data-lucide="wheat" style="width:15px;height:15px;"></i> Raw Materials Store</a>
        <a href="/Frontend/admin/feed_production.php" class="btn btn-primary"><i data-lucide="flask-conical" style="width:15px;height:15px;"></i> Make Feed</a>
    </div>
</div>

<!-- Tab Navigation -->
<div style="display:flex;gap:4px;background:#f1f5f9;padding:5px;border-radius:10px;margin-bottom:24px;overflow-x:auto;">
    <a href="?tab=overview" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;<?= $tab==='overview'?'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);':'color:#64748b;' ?>"><i data-lucide="layout-dashboard" style="width:15px;height:15px;"></i> Warehouse Overview</a>
    <a href="/Frontend/admin/feed_production.php?tab=stock" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;color:#64748b;"><i data-lucide="package" style="width:15px;height:15px;"></i> Finished Feeds Stock</a>
    <a href="/Frontend/admin/stores.php" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;color:#64748b;"><i data-lucide="wheat" style="width:15px;height:15px;"></i> Raw Materials Ledger</a>
    <a href="/Frontend/admin/products.php" style="display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;color:#64748b;"><i data-lucide="list" style="width:15px;height:15px;"></i> Full Product Catalog</a>
</div>

<!-- Three Core Asset Pillars -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:18px;margin-bottom:24px;">
    <!-- Pillar 1: Finished Feeds -->
    <div class="hub-card" style="border-top:4px solid #16a34a;">
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span style="font-size:0.8rem;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:0.05em;">Finished Goods</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="package-check" style="width:18px;height:18px;"></i>
                </div>
            </div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;color:#0f172a;"><?= number_format($feedBagsCount) ?> Bags</h3>
            <p style="margin:4px 0 14px;color:#64748b;font-size:0.88rem;">Finished feeds in warehouse ready for delivery or farm feeding.</p>
            <div style="padding:10px 14px;background:#f8fafc;border-radius:8px;margin-bottom:16px;">
                <small style="color:#64748b;">Estimated Inventory Value:</small>
                <strong style="display:block;font-size:1.15rem;color:#15803d;">KES <?= number_format($feedStockVal, 2) ?></strong>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="/Frontend/admin/feed_production.php?tab=produce" class="btn btn-outline" style="flex:1;text-align:center;"><i data-lucide="play" style="width:14px;height:14px;"></i> Mill Feeds</a>
            <a href="/Frontend/admin/bulk_sales.php?product=feed" class="btn btn-primary" style="flex:1;text-align:center;"><i data-lucide="shopping-cart" style="width:14px;height:14px;"></i> Sell Feeds</a>
        </div>
    </div>

    <!-- Pillar 2: Raw Materials -->
    <div class="hub-card" style="border-top:4px solid #d97706;">
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span style="font-size:0.8rem;font-weight:700;color:#d97706;text-transform:uppercase;letter-spacing:0.05em;">Raw Materials Store</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="wheat" style="width:18px;height:18px;"></i>
                </div>
            </div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;color:#0f172a;"><?= number_format($rawMaterialsKg, 1) ?> kg</h3>
            <p style="margin:4px 0 14px;color:#64748b;font-size:0.88rem;">Maize, soya, wheat bran, premixes, and lime available in silos.</p>
            <div style="padding:10px 14px;background:#f8fafc;border-radius:8px;margin-bottom:16px;">
                <small style="color:#64748b;">Current Stock Valuation:</small>
                <strong style="display:block;font-size:1.15rem;color:#b45309;">KES <?= number_format($rawMaterialsVal, 2) ?></strong>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="/Frontend/admin/stores.php?tab=ingredients" class="btn btn-outline" style="flex:1;text-align:center;"><i data-lucide="list" style="width:14px;height:14px;"></i> View Stock</a>
            <a href="/Frontend/admin/stores.php?tab=movements" class="btn btn-primary" style="flex:1;text-align:center;"><i data-lucide="plus" style="width:14px;height:14px;"></i> Intake Grain</a>
        </div>
    </div>

    <!-- Pillar 3: Live Poultry Flocks -->
    <div class="hub-card" style="border-top:4px solid #2563eb;">
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span style="font-size:0.8rem;font-weight:700;color:#2563eb;text-transform:uppercase;letter-spacing:0.05em;">Live Poultry Asset</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="bird" style="width:18px;height:18px;"></i>
                </div>
            </div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;color:#0f172a;"><?= number_format($liveBirdsCount) ?> Birds</h3>
            <p style="margin:4px 0 14px;color:#64748b;font-size:0.88rem;">Active across <?= $activeBatchesCount ?> farm houses (Broilers, Layers, Kienyeji).</p>
            <div style="padding:10px 14px;background:#f8fafc;border-radius:8px;margin-bottom:16px;">
                <small style="color:#64748b;">Flock Status:</small>
                <strong style="display:block;font-size:1.15rem;color:#1d4ed8;"><?= $activeBatchesCount ?> Active Batches</strong>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="/Frontend/admin/batches.php" class="btn btn-outline" style="flex:1;text-align:center;"><i data-lucide="layers" style="width:14px;height:14px;"></i> Batches</a>
            <a href="/Frontend/admin/bulk_sales.php?product=broiler" class="btn btn-primary" style="flex:1;text-align:center;"><i data-lucide="shopping-cart" style="width:14px;height:14px;"></i> Sell Birds</a>
        </div>
    </div>
</div>

<!-- Low Stock Alerts (ERPNext Reorder Level check) -->
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;display:flex;align-items:center;gap:8px;">
            <i data-lucide="alert-triangle" style="width:18px;height:18px;color:#d97706;"></i> Reorder & Low Stock Alerts
        </h3>
        <a href="/Frontend/admin/stores.php" class="btn btn-trans btn-sm">Manage Stores</a>
    </div>

    <?php if (empty($lowStockItems)): ?>
        <div style="padding:24px;text-align:center;color:#166534;background:#f0fdf4;border-radius:8px;font-size:0.9rem;">
            <i data-lucide="check-circle" style="width:22px;height:22px;margin:0 auto 6px;display:block;"></i>
            All feed ingredients and warehouse items are above minimum stock levels.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Ingredient</th><th>Current Stock</th><th>Minimum Level</th><th>Shortage</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($lowStockItems as $l): 
                    $cur = (float)$l['current_stock'];
                    $min = (float)$l['min_stock_level'];
                ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($l['material_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td style="color:#dc2626;font-weight:700;"><?= $cur ?> <?= htmlspecialchars($l['unit'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $min ?> <?= htmlspecialchars($l['unit'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge-pill badge-pill-danger">Low by <?= max(0, $min - $cur) ?> <?= htmlspecialchars($l['unit'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>
                            <a href="/Frontend/admin/stores.php?tab=movements" class="btn btn-primary btn-sm"><i data-lucide="plus" style="width:12px;height:12px;"></i> Reorder / Intake</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/admin_footer.php';
