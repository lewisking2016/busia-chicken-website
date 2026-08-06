<?php
/**
 * Admin Sidebar — Clean 6-item navigation with dropdown submodules.
 */
declare(strict_types=1);

$cp   = basename($_SERVER['SCRIPT_NAME']);
$tab  = $_GET['tab'] ?? '';

// Active hub detection
$isOps      = $cp === 'hub_operations.php';
$isInventory= $cp === 'hub_inventory.php';
$isFinance  = $cp === 'hub_finance.php';
$isPeople   = $cp === 'hub_people.php';
$isSettings = $cp === 'hub_settings.php';
$isDash     = $cp === 'dashboard.php';

function navLinkWithSub(string $href, string $icon, string $label, bool $active, array $submodules, string $currentTab): string {
    $base = $active
        ? 'background:linear-gradient(135deg,#1B5E20,#2E7D32);color:#fff;box-shadow:0 4px 14px rgba(27,94,32,0.22);'
        : 'color:#475569;';
    $subActiveStyle = $active ? 'transform: rotate(180deg);' : '';
    $html = <<<HTML
    <li style="margin-bottom: 2px;">
        <a href="{$href}"
           style="display:flex;align-items:center;gap:13px;padding:11px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;transition:all 0.18s cubic-bezier(0.4,0,0.2,1);border:1px solid transparent;{$base}">
            <i data-lucide="{$icon}" style="width:19px;height:19px;flex-shrink:0;"></i>
            <span style="flex-grow: 1;">{$label}</span>
            <i data-lucide="chevron-down" style="width:14px;height:14px;transition: transform 0.2s; {$subActiveStyle}"></i>
        </a>
    </li>
HTML;

    if ($active && !empty($submodules)) {
        $html .= '<ul style="list-style:none; padding-left:24px; margin:4px 0 8px 0; display:flex; flex-direction:column; gap:4px; border-left: 2px solid rgba(27,94,32,0.15);">';
        foreach ($submodules as $tKey => $tName) {
            $subActive = ($currentTab === $tKey);
            $subColor = $subActive ? 'color: var(--admin-primary); font-weight: 700;' : 'color: #64748b; font-weight: 500;';
            $html .= <<<HTML
            <li>
                <a href="{$href}?tab={$tKey}" style="display:block; padding:6px 12px; font-size:0.82rem; text-decoration:none; border-radius:4px; transition: all 0.15s; {$subColor}" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                    • {$tName}
                </a>
            </li>
HTML;
        }
        $html .= '</ul>';
    }

    return $html;
}
?>
<nav style="width:264px;background:#fff;border-right:1px solid rgba(203,213,225,0.7);padding:18px 14px;position:sticky;top:0;height:100vh;display:flex;flex-direction:column;box-shadow:2px 0 16px rgba(15,23,42,0.03);box-sizing:border-box;z-index:100;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(27,94,32,0.15) transparent;flex-shrink:0;">

    <!-- Brand -->
    <div style="display:flex;align-items:center;gap:11px;margin-bottom:28px;padding:0 4px;">
        <img src="/Frontend/images/busia logo.png" alt="Busia Chicken" style="height:44px;width:auto;border-radius:8px;">
        <div>
            <p style="margin:0;font-family:'Outfit',sans-serif;font-size:1.05rem;font-weight:800;color:#0f172a;letter-spacing:-0.3px;">Busia Chicken</p>
            <small style="display:block;color:#64748b;font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Admin Console</small>
        </div>
    </div>

    <!-- Navigation -->
    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:5px;flex-grow:1;">

        <li>
            <a href="/Frontend/admin/dashboard.php"
               style="display:flex;align-items:center;gap:13px;padding:11px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;transition:all 0.18s cubic-bezier(0.4,0,0.2,1);border:1px solid transparent;<?= $isDash ? 'background:linear-gradient(135deg,#1B5E20,#2E7D32);color:#fff;' : 'color:#475569;' ?>">
                <i data-lucide="layout-dashboard" style="width:19px;height:19px;flex-shrink:0;"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <?php
        echo navLinkWithSub(
            '/Frontend/admin/hub_operations.php',
            'bird',
            'Farm Operations',
            $isOps,
            [
                'flocks'       => 'Flocks / Herds',
                'production'   => 'Daily Production',
                'vaccinations' => 'Vaccinations',
                'animals'      => 'Animals List',
                'health'       => 'Health Records',
                'breeding'     => 'Breeding Events',
                'herds'        => 'Herds / Pens'
            ],
            $tab ?: 'flocks'
        );

        echo navLinkWithSub(
            '/Frontend/admin/hub_inventory.php',
            'package',
            'Inventory & Store',
            $isInventory,
            [
                'products'  => 'Products Catalog',
                'equipment' => 'Farm Equipment',
                'feedstock' => 'Feed & Stock',
                'alerts'    => 'Inventory Alerts'
            ],
            $tab ?: 'products'
        );

        echo navLinkWithSub(
            '/Frontend/admin/hub_finance.php',
            'trending-up',
            'Sales & Finance',
            $isFinance,
            [
                'orders'   => 'Customer Orders',
                'sales'    => 'Sales Register',
                'payments' => 'Incoming Payments',
                'expenses' => 'Outgoing Expenses',
                'reports'  => 'Reports & Charts'
            ],
            $tab ?: 'orders'
        );

        echo navLinkWithSub(
            '/Frontend/admin/hub_people.php',
            'users',
            'Team & Messages',
            $isPeople,
            [
                'staff'    => 'Staff Accounts',
                'users'    => 'Customer List',
                'tasks'    => 'Assigned Tasks',
                'messages' => 'Team Messages'
            ],
            $tab ?: 'staff'
        );

        echo navLinkWithSub(
            '/Frontend/admin/hub_settings.php',
            'settings',
            'Settings',
            $isSettings,
            [
                'calendar'  => 'Calendar View',
                'dropdowns' => 'Dropdown Config',
                'settings'  => 'App Settings',
                'logs'      => 'System Logs',
                'setup'     => 'DB Setup'
            ],
            $tab ?: 'calendar'
        );
        ?>

        <li style="margin-bottom: 2px;">
            <a href="/Frontend/admin/bulk_import_export.php"
               style="display:flex;align-items:center;gap:13px;padding:11px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;transition:all 0.18s cubic-bezier(0.4,0,0.2,1);border:1px solid transparent;<?= $cp === 'bulk_import_export.php' ? 'background:linear-gradient(135deg,#1B5E20,#2E7D32);color:#fff;box-shadow:0 4px 14px rgba(27,94,32,0.22);' : 'color:#475569;' ?>">
                <i data-lucide="database" style="width:19px;height:19px;flex-shrink:0;"></i>
                <span>Bulk Import/Export</span>
            </a>
        </li>

    </ul>

    <!-- User info & logout -->
    <div style="margin-top:auto;padding-top:14px;border-top:1px solid rgba(203,213,225,0.6);">
        <!-- Profile badge -->
        <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f8fafc;border-radius:8px;margin-bottom:10px;">
            <div style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,#1B5E20,#FFC107);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-family:'Outfit',sans-serif;font-size:0.95rem;flex-shrink:0;">
                <?php echo strtoupper(substr($_SESSION['first_name'] ?? $_SESSION['username'] ?? 'A', 0, 1)); ?>
            </div>
            <div style="min-width:0;">
                <p style="margin:0;font-size:0.88rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></p>
                <span style="font-size:0.7rem;color:#64748b;text-transform:capitalize;"><?php echo htmlspecialchars(str_replace('_', ' ', $_SESSION['role'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>

        <!-- Logout -->
        <a href="/Frontend/pages/logout.php"
           style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px;border-radius:8px;background:#fee2e2;color:#b91c1c;text-decoration:none;font-weight:600;font-size:0.88rem;transition:background 0.18s;"
           onmouseover="this.style.background='#fca5a5'" onmouseout="this.style.background='#fee2e2'">
            <i data-lucide="log-out" style="width:16px;height:16px;"></i>
            Sign Out
        </a>
    </div>
</nav>
