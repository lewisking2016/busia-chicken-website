<?php
/**
 * Admin Sidebar — Complete Poultry Management Navigation
 * 6 hub modules with sub-tabs each
 */
declare(strict_types=1);

$cp   = basename($_SERVER['SCRIPT_NAME']);
$tab  = $_GET['tab'] ?? '';

$isDash       = $cp === 'dashboard.php';
$isPoultry    = in_array($cp, ['hub_operations.php','flocks.php','production.php','vaccinations.php','batches.php','health.php','broiler.php','hatchery.php','feeding.php','extras.php'], true);
$isInventory  = in_array($cp, ['hub_inventory.php','stores.php','feed_production.php','egg_grading.php'], true);
$isSalesFinance = in_array($cp, ['hub_finance.php','profit.php','cashbook.php','credit.php','purchase_orders.php','daily_sales.php','bulk_sales.php','lpo.php'], true);
$isReports    = in_array($cp, ['analytics.php','bulk_import_export.php'], true);
$isPeople     = $cp === 'hub_people.php';
$isSettings   = $cp === 'hub_settings.php';

function navLinkWithSub(string $href, string $icon, string $label, bool $active, array $submodules, string $currentTab): string {
    // Permission filtering: hide sub-modules the current role cannot view.
    $visible = [];
    foreach ($submodules as $tKey => $item) {
        if (is_array($item)) {
            $permKey = $item['perm'] ?? '';
            if ($permKey === '' && function_exists('busiaModuleKeyForScript')) {
                $permKey = busiaModuleKeyForScript(basename(parse_url($item['href'] ?? '', PHP_URL_PATH)));
            }
            if ($permKey === '') $permKey = (string)$tKey;
        } else {
            $permKey = (string)$tKey;
        }
        if (function_exists('busiaCanView') && !busiaCanView($permKey)) {
            continue; // role has no view permission for this sub-module
        }
        $visible[$tKey] = $item;
    }
    if (empty($visible) && !$active) {
        return ''; // nothing viewable — hide the whole group
    }
    $submodules = $visible;

    $base = $active
        ? 'background:linear-gradient(135deg,#1B5E20,#2E7D32);color:#fff;box-shadow:0 4px 14px rgba(27,94,32,0.22);'
        : 'color:#475569;';
    $linkClass = 'nav-item' . ($active ? ' active' : '');
    // Only the active group is expanded by default; the rest stay collapsed
    // (chevron toggles them client-side, and the state is remembered).
    $groupClass = $active ? ' nav-group-open' : '';
    $subsDisplay = $active ? 'flex' : 'none';
    $chevronColor = $active ? 'rgba(255,255,255,0.9)' : '#94a3b8';
    $html = <<<HTML
    <li class="nav-group{$groupClass}" style="margin-bottom: 2px;">
        <div style="display:flex;align-items:center;">
            <a href="{$href}" class="{$linkClass}"
               style="display:flex;align-items:center;gap:13px;padding:11px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;transition:all 0.18s cubic-bezier(0.4,0,0.2,1);border:1px solid transparent;flex-grow:1;{$base}">
                <i data-lucide="{$icon}" style="width:19px;height:19px;flex-shrink:0;"></i>
                <span style="flex-grow: 1;">{$label}</span>
            </a>
            <button type="button" class="nav-chevron" data-nav-group-key="{$href}" aria-label="Toggle {$label} section"
                    style="background:none;border:none;cursor:pointer;padding:13px 12px 13px 2px;color:{$chevronColor};line-height:0;">
                <i data-lucide="chevron-down" style="width:15px;height:15px;display:block;"></i>
            </button>
        </div>
        <ul class="nav-subs" style="list-style:none;padding-left:24px;margin:2px 0 8px 0;flex-direction:column;gap:4px;border-left:2px solid rgba(27,94,32,0.15);display:{$subsDisplay};">
HTML;

    foreach ($submodules as $tKey => $item) {
        if (is_array($item) && isset($item['label'], $item['href'])) {
            $linkHref = $item['href'];
            $subLabel = $item['label'];
            $subActive = basename($_SERVER['SCRIPT_NAME']) === basename(parse_url($linkHref, PHP_URL_PATH));
        } else {
            $linkHref = "{$href}?tab={$tKey}";
            $subLabel = (string)$item;
            // String (hub-tab) items are only active when we are actually on
            // their hub page — otherwise e.g. "Flocks" would stay highlighted
            // on every other page inside the same group.
            $isHubPage = basename($_SERVER['SCRIPT_NAME']) === basename(parse_url($href, PHP_URL_PATH));
            $subActive = $isHubPage && $currentTab === $tKey;
        }
        $subColor = $subActive ? 'color: var(--admin-primary); font-weight: 700;' : 'color: #64748b; font-weight: 500;';
        $subClass = 'nav-sub' . ($subActive ? ' active' : '');
        $html .= <<<HTML
            <li>
                <a href="{$linkHref}" class="{$subClass}" style="display:block; padding:6px 12px; font-size:0.82rem; text-decoration:none; border-radius:4px; transition: all 0.15s; {$subColor}" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                    • {$subLabel}
                </a>
            </li>
HTML;
    }
    $html .= '</ul></li>';

    return $html;
}

function navLinkDirect(string $href, string $icon, string $label, bool $active): string {
    $base = $active
        ? 'background:linear-gradient(135deg,#1B5E20,#2E7D32);color:#fff;box-shadow:0 4px 14px rgba(27,94,32,0.22);'
        : 'color:#475569;';
    $linkClass = 'nav-item' . ($active ? ' active' : '');
    return <<<HTML
    <li style="margin-bottom: 2px;">
        <a href="{$href}" class="{$linkClass}" style="display:flex;align-items:center;gap:13px;padding:11px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;transition:all 0.18s cubic-bezier(0.4,0,0.2,1);border:1px solid transparent;{$base}">
            <i data-lucide="{$icon}" style="width:19px;height:19px;flex-shrink:0;"></i>
            <span>{$label}</span>
        </a>
    </li>
HTML;
}
?>
<style>
    .nav-chevron svg { transition: transform 0.2s ease; }
    .nav-group-open .nav-chevron svg { transform: rotate(180deg); }
    .nav-subs { transition: opacity 0.15s ease; }
</style>
<nav id="admin-nav" style="width:264px;background:#fff;border-right:1px solid rgba(203,213,225,0.7);padding:18px 14px;position:sticky;top:0;height:100vh;display:flex;flex-direction:column;box-shadow:2px 0 16px rgba(15,23,42,0.03);box-sizing:border-box;z-index:100;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(27,94,32,0.15) transparent;flex-shrink:0;">

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

        <?= navLinkDirect('/Frontend/admin/dashboard.php','layout-dashboard','Dashboard',$isDash) ?>

        <?= navLinkWithSub(
            '/Frontend/admin/hub_operations.php',
            'bird',
            'Poultry Operations',
            $isPoultry,
            [
                'flocks'       => 'Flocks',
                'production'   => 'Daily Production',
                'vaccinations' => 'Vaccinations',
                'batches'      => ['label' => 'Batches & Houses', 'href' => '/Frontend/admin/batches.php'],
                'health'       => ['label' => 'Health & Vet', 'href' => '/Frontend/admin/health.php'],
                'broiler'      => ['label' => 'Broiler Workflow', 'href' => '/Frontend/admin/broiler.php'],
                'hatchery'     => ['label' => 'Hatchery (DOC)', 'href' => '/Frontend/admin/hatchery.php'],
                'feeding'      => ['label' => 'Feeding Program', 'href' => '/Frontend/admin/feeding.php'],
                'extras'       => ['label' => 'Losses & Quality', 'href' => '/Frontend/admin/extras.php']
            ],
            $tab ?: 'flocks'
        ) ?>

        <?= navLinkWithSub(
            '/Frontend/admin/hub_inventory.php',
            'package',
            'Inventory & Stores',
            $isInventory,
            [
                'products' => 'Products Catalog',
                'stores'   => ['label' => 'Stores & Stock', 'href' => '/Frontend/admin/stores.php'],
                'feed'     => ['label' => 'Feed Production', 'href' => '/Frontend/admin/feed_production.php'],
                'eggs'     => ['label' => 'Egg Grading', 'href' => '/Frontend/admin/egg_grading.php']
            ],
            $tab ?: 'products'
        ) ?>

        <?= navLinkWithSub(
            '/Frontend/admin/hub_finance.php',
            'trending-up',
            'Sales & Finance',
            $isSalesFinance,
            [
                'hub_finance' => ['label' => 'Sales & Finance Hub', 'href' => '/Frontend/admin/hub_finance.php'],
                'profit'      => ['label' => 'Costs & Profit', 'href' => '/Frontend/admin/profit.php'],
                'cashbook'    => ['label' => 'Cashbook', 'href' => '/Frontend/admin/cashbook.php'],
                'credit'      => ['label' => 'Customer Credit', 'href' => '/Frontend/admin/credit.php'],
                'lpo'         => ['label' => 'LPO & Invoicing', 'href' => '/Frontend/admin/lpo.php'],
                'po'          => ['label' => 'Procurement (PO)', 'href' => '/Frontend/admin/purchase_orders.php'],
                'daily'       => ['label' => 'Daily Reconciliation', 'href' => '/Frontend/admin/daily_sales.php'],
                'bulk'        => ['label' => 'Bulk Sales', 'href' => '/Frontend/admin/bulk_sales.php']
            ],
            $tab ?: 'hub_finance'
        ) ?>

        <?= navLinkWithSub(
            '/Frontend/admin/analytics.php',
            'bar-chart-2',
            'Reports & Tools',
            $isReports,
            [
                'analytics' => ['label' => 'Analytics & Charts', 'href' => '/Frontend/admin/analytics.php'],
                'import'    => ['label' => 'Bulk Import/Export', 'href' => '/Frontend/admin/bulk_import_export.php']
            ],
            $tab ?: 'analytics'
        ) ?>

        <?= navLinkWithSub(
            '/Frontend/admin/hub_people.php',
            'users',
            'Team & Messages',
            $isPeople,
            [
                'staff'    => 'Staff',
                'users'    => 'Customers',
                'tasks'    => 'Tasks',
                'messages' => 'Messages'
            ],
            $tab ?: 'staff'
        ) ?>

        <?= navLinkWithSub(
            '/Frontend/admin/hub_settings.php',
            'settings',
            'Settings',
            $isSettings,
            [
                'calendar'  => 'Calendar',
                'dropdowns' => 'Dropdowns',
                'settings'  => 'App Settings',
                'logs'      => 'Activity Logs',
                'permissions' => ['label' => 'Roles & Permissions', 'href' => '/Frontend/admin/permissions.php']
            ],
            $tab ?: 'calendar'
        ) ?>

    </ul>

    <!-- User info & logout -->
    <div style="margin-top:auto;padding-top:14px;border-top:1px solid rgba(203,213,225,0.6);">
        <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f8fafc;border-radius:8px;margin-bottom:10px;">
            <div style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,#1B5E20,#FFC107);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-family:'Outfit',sans-serif;font-size:0.95rem;flex-shrink:0;">
                <?php echo strtoupper(substr($_SESSION['first_name'] ?? $_SESSION['username'] ?? 'A', 0, 1)); ?>
            </div>
            <div style="min-width:0;">
                <p style="margin:0;font-size:0.88rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></p>
                <span style="font-size:0.7rem;color:#64748b;text-transform:capitalize;"><?php echo htmlspecialchars(str_replace('_', ' ', $_SESSION['role'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
        <a href="/Frontend/pages/logout.php" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px;border-radius:8px;background:#fee2e2;color:#b91c1c;text-decoration:none;font-weight:600;font-size:0.88rem;transition:background 0.18s;" onmouseover="this.style.background='#fca5a5'" onmouseout="this.style.background='#fee2e2'">
            <i data-lucide="log-out" style="width:16px;height:16px;"></i> Sign Out
        </a>
    </div>
</nav>
