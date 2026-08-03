<?php
/**
 * Admin sidebar navigation for dashboard pages.
 */
declare(strict_types=1);
$currentPage = basename($_SERVER['SCRIPT_NAME']);
function isActivePage(string $page, string $current): string {
    return $current === $page ? 'active' : '';
}
?>
<nav class="admin-sidebar">
    <div class="admin-sidebar-brand">
        <img src="/Frontend/images/busia logo.png" alt="Busia Chicken" style="height: 48px; width: auto;">
        <div>
            <p>Busia Admin</p>
            <small>Manager Console</small>
        </div>
    </div>
    <ul class="admin-sidebar-nav">
        <li>
            <a href="/Frontend/admin/dashboard.php" class="<?php echo isActivePage('dashboard.php', $currentPage); ?>">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <?php if ($isAdmin): ?>
        <li>
            <a href="/Frontend/admin/products.php" class="<?php echo isActivePage('products.php', $currentPage); ?>">
                <i data-lucide="package"></i>
                <span>Products</span>
            </a>
        </li>
        <li>
            <a href="/Frontend/admin/orders.php" class="<?php echo isActivePage('orders.php', $currentPage); ?>">
                <i data-lucide="shopping-bag"></i>
                <span>Orders</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Stock Brain Dropdown (Visible to all Admins & Stock Managers) -->
        <?php $isStockActive = str_contains($currentPage, 'stock_') || $currentPage === 'incoming_stock.php'; ?>
        <li class="has-dropdown">
            <a href="javascript:void(0)" class="dropdown-trigger <?php echo $isStockActive ? 'open' : ''; ?>" onclick="toggleDropdown('stock-dropdown', this)">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i data-lucide="brain-circuit"></i>
                    <span>Feed & Stock</span>
                </div>
                <i data-lucide="chevron-down" class="chevron"></i>
            </a>
            <ul class="sidebar-dropdown <?php echo $isStockActive ? 'open' : ''; ?>" id="stock-dropdown">
                <li>
                    <a href="/Frontend/admin/stock_dashboard.php" class="<?php echo isActivePage('stock_dashboard.php', $currentPage); ?>">
                        <i data-lucide="layout-dashboard"></i>
                        <span>Stock Overview</span>
                    </a>
                </li>
                <li>
                    <a href="/Frontend/admin/stock_formula_center.php" class="<?php echo isActivePage('stock_formula_center.php', $currentPage); ?>">
                        <i data-lucide="flask-conical"></i>
                        <span>Feed Recipes</span>
                    </a>
                </li>
                <li>
                    <a href="/Frontend/admin/incoming_stock.php" class="<?php echo isActivePage('incoming_stock.php', $currentPage); ?>">
                        <i data-lucide="package-plus"></i>
                        <span>Buy Ingredients <small style="font-weight:400; opacity:0.75;">(Raw Materials)</small></span>
                    </a>
                </li>
                <li>
                    <a href="/Frontend/admin/stock_alerts.php" class="<?php echo isActivePage('stock_alerts.php', $currentPage); ?>">
                        <i data-lucide="bell"></i>
                        <span>Alert Center</span>
                    </a>
                </li>
            </ul>
        </li>

        <?php if ($isAdmin): ?>
        <li>
            <a href="/Frontend/admin/reports.php" class="<?php echo isActivePage('reports.php', $currentPage); ?>">
                <i data-lucide="bar-chart-3"></i>
                <span>Reports</span>
            </a>
        </li>
        <li>
            <a href="/Frontend/admin/users.php" class="<?php echo isActivePage('users.php', $currentPage); ?>">
                <i data-lucide="users"></i>
                <span>Users</span>
            </a>
        </li>
        <li>
            <a href="/Frontend/admin/dropdowns.php" class="<?php echo isActivePage('dropdowns.php', $currentPage); ?>">
                <i data-lucide="list-filter"></i>
                <span>Dropdown Manager</span>
            </a>
        </li>
        <li>
            <a href="/Frontend/admin/settings.php" class="<?php echo isActivePage('settings.php', $currentPage); ?>">
                <i data-lucide="settings"></i>
                <span>Settings</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <div class="admin-sidebar-footer">
        <a href="/Frontend/pages/logout.php" class="btn btn-outline" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i data-lucide="log-out" style="width: 18px; height: 18px;"></i>
            <span>Sign Out</span>
        </a>
    </div>
</nav>

<script>
function toggleDropdown(id, trigger) {
    const dropdown = document.getElementById(id);
    const isOpen = dropdown.classList.contains('open');
    
    // Close all other dropdowns if any (optional, but good for UX)
    document.querySelectorAll('.sidebar-dropdown').forEach(el => {
        if (el.id !== id) {
            el.classList.remove('open');
            el.previousElementSibling.classList.remove('open');
        }
    });

    // Toggle current
    if (isOpen) {
        dropdown.classList.remove('open');
        trigger.classList.remove('open');
    } else {
        dropdown.classList.add('open');
        trigger.classList.add('open');
    }
}
</script>
