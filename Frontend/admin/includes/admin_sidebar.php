<?php
/**
 * Admin Sidebar — Clean 6-item navigation.
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

function navLink(string $href, string $icon, string $label, bool $active): string {
    $base = $active
        ? 'background:linear-gradient(135deg,#1B5E20,#2E7D32);color:#fff;box-shadow:0 4px 14px rgba(27,94,32,0.22);'
        : 'color:#475569;';
    return <<<HTML
    <li>
        <a href="{$href}"
           style="display:flex;align-items:center;gap:13px;padding:11px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;transition:all 0.18s cubic-bezier(0.4,0,0.2,1);border:1px solid transparent;{$base}">
            <i data-lucide="{$icon}" style="width:19px;height:19px;flex-shrink:0;"></i>
            <span>{$label}</span>
        </a>
    </li>
    HTML;
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

        <?php echo navLink('/Frontend/admin/dashboard.php',      'layout-dashboard', 'Dashboard',         $isDash);     ?>
        <?php echo navLink('/Frontend/admin/hub_operations.php', 'bird',             'Farm Operations',   $isOps);      ?>
        <?php echo navLink('/Frontend/admin/hub_inventory.php',  'package',          'Inventory & Store', $isInventory);?>
        <?php echo navLink('/Frontend/admin/hub_finance.php',    'trending-up',      'Sales & Finance',   $isFinance);  ?>
        <?php echo navLink('/Frontend/admin/hub_people.php',     'users',            'Team & Messages',   $isPeople);   ?>
        <?php echo navLink('/Frontend/admin/hub_settings.php',   'settings',         'Settings',          $isSettings); ?>

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
