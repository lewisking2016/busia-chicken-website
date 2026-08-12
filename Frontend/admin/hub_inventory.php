<?php
/**
 * Hub: Inventory & Store — Poultry-Only Edition
 * Tabs: Products Catalog
 */
declare(strict_types=1);
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager','stock_manager','sales_staff'], true)) {
    echo "<script>window.location.href='/busiaadmin';</script>"; exit;
}

$page_title = 'Inventory & Store - Admin';
include __DIR__ . '/includes/admin_header.php';

$tab = $_GET['tab'] ?? 'products';
$validTabs = ['products'];
if (!in_array($tab, $validTabs, true)) $tab = 'products';

$pdo = getDB();
$message = ''; $error_message = '';

$tabs = [
    'products' => ['icon' => 'package', 'label' => 'Products Catalog'],
];
?>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;">Inventory & Store</h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:0.9rem;">Manage your poultry product catalog — chickens, eggs, chicks, and feeds.</p>
    </div>
</div>

<?php if ($message): ?>
<div style="padding:13px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:8px;color:#166534;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i> <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:13px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:8px;color:#b91c1c;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <i data-lucide="alert-circle" style="width:18px;height:18px;"></i> <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<!-- Tab Bar -->
<div style="display:flex;gap:4px;background:#f1f5f9;padding:5px;border-radius:10px;margin-bottom:24px;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none;">
<?php foreach ($tabs as $key => $info): ?>
    <a href="?tab=<?= $key ?>" style="display:flex;align-items:center;gap:7px;padding:9px 14px;border-radius:7px;text-decoration:none;white-space:nowrap;font-weight:600;font-size:0.84rem;transition:all 0.18s;<?= $tab===$key ? 'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);' : 'color:#64748b;' ?>">
        <i data-lucide="<?= $info['icon'] ?>" style="width:15px;height:15px;"></i><?= $info['label'] ?>
    </a>
<?php endforeach; ?>
</div>

<!-- ══════ PRODUCTS CATALOG TAB ══════ -->
<?php if ($tab === 'products'): ?>
<div class="admin-card" style="padding:0; overflow:hidden;">
    <iframe src="products.php" style="width:100%; height:800px; border:none; display:block;"></iframe>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php';
