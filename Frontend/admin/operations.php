<?php
/**
 * Consolidated Admin - Livestock & Poultry Operations
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Livestock & Poultry - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager', 'sales_staff'], true)) {
    echo "<script>window.location.href = '/busiaadmin';</script>";
    exit;
}

// The Animals / Herds / Breeding / Health sections live in the maintained
// hub pages (hub_operations.php, hub_mybirds.php). Those tab partials were
// removed from the admin panel, so this consolidated page keeps only the
// Flocks manager that still exists here.
$tab = $_GET['tab'] ?? 'flocks';
$allowedTabs = ['flocks'];
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'flocks';
}
?>

<!-- Flock Manager Content -->
<div class="operations-tab-content">
    <?php
    $path_prefix = '../../';
    include __DIR__ . '/flocks_tab.php';
    ?>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
