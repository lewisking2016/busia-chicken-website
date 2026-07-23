<?php
/**
 * Shared navigation for Stock Brain sub-modules.
 */
declare(strict_types=1);

$currentStockPage = basename($_SERVER['SCRIPT_NAME']);
?>

<div class="stock-sub-nav">
    <a href="/Frontend/admin/stock_dashboard.php" class="stock-nav-item <?php echo $currentStockPage === 'stock_dashboard.php' ? 'active' : ''; ?>">
        <i data-lucide="layout-dashboard"></i>
        <span>Live Dashboard</span>
    </a>
    <a href="/Frontend/admin/stock_formula_center.php" class="stock-nav-item <?php echo $currentStockPage === 'stock_formula_center.php' ? 'active' : ''; ?>">
        <i data-lucide="flask-conical"></i>
        <span>Formula Center</span>
    </a>
    <a href="/Frontend/admin/stock_alerts.php" class="stock-nav-item <?php echo $currentStockPage === 'stock_alerts.php' ? 'active' : ''; ?>">
        <i data-lucide="bell"></i>
        <span>Alert Center</span>
    </a>
</div>
