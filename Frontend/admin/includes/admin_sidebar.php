<?php
/**
 * Admin Sidebar — Complete Poultry Management Navigation
 * 6 hub modules with sub-tabs each
 */
declare(strict_types=1);

$cp   = basename($_SERVER['SCRIPT_NAME']);
$tab  = $_GET['tab'] ?? '';

$isDash       = $cp === 'dashboard.php';
$isPoultry    = in_array($cp, ['hub_operations.php','hub_mybirds.php','flocks.php','production.php','vaccinations.php','batches.php','health.php','broiler.php','hatchery.php','feeding.php','extras.php'], true);
$isInventory  = in_array($cp, ['hub_inventory.php','stores.php','feed_production.php','egg_grading.php'], true);
$isSalesFinance = in_array($cp, ['hub_money.php','hub_finance.php','profit.php','cashbook.php','credit.php','purchase_orders.php','daily_sales.php','bulk_sales.php','lpo.php'], true);
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

    // Small icon for every sub-item so eyes can scan faster than reading text.
    $subIcons = [
        'flocks' => 'layers', 'production' => 'egg', 'vaccinations' => 'syringe',
        'batches' => 'home', 'health' => 'heart-pulse', 'broiler' => 'drumstick',
        'hatchery' => 'egg', 'feeding' => 'wheat', 'extras' => 'heart-crack',
        'products' => 'package', 'stores' => 'boxes', 'feed' => 'flask-conical', 'eggs' => 'egg',
        'hub_finance' => 'banknote', 'profit' => 'percent', 'cashbook' => 'book-open',
        'credit' => 'hand-coins', 'lpo' => 'file-text', 'po' => 'shopping-cart',
        'daily' => 'receipt', 'bulk' => 'shopping-bag',
        'analytics' => 'bar-chart-3', 'import' => 'download',
        'staff' => 'users', 'users' => 'user', 'tasks' => 'check-circle', 'messages' => 'message-circle',
        'calendar' => 'calendar', 'dropdowns' => 'list', 'settings' => 'settings',
        'logs' => 'scroll-text', 'permissions' => 'shield-check',
        'group_flocks' => 'bird', 'group_eggs' => 'egg', 'group_health' => 'heart-pulse', 'group_growth' => 'trending-up',
        'money_overview' => 'layout-dashboard', 'money_income' => 'trending-up', 'money_spending' => 'wallet',
    ];

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
            // Hub pages share one script (e.g. hub_mybirds.php) — a sub item is
            // active only when its ?group= matches the page we are actually on.
            if (basename($_SERVER['SCRIPT_NAME']) === basename(parse_url($linkHref, PHP_URL_PATH))) {
                parse_str((string)parse_url($linkHref, PHP_URL_QUERY), $linkQp);
                $subActive = ($linkQp['group'] ?? '') === ($_GET['group'] ?? '');
                if (!isset($linkQp['group'])) $subActive = true; // plain pages are always active on themselves
            } else {
                $subActive = false;
            }
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
        $subIcon = $subIcons[$tKey] ?? 'arrow-right';
        $html .= <<<HTML
            <li>
                <a href="{$linkHref}" class="{$subClass}" style="display:flex;align-items:center;gap:8px; padding:7px 10px; font-size:0.82rem; text-decoration:none; border-radius:6px; transition: all 0.15s; {$subColor}">
                    <i data-lucide="{$subIcon}" style="width:14px;height:14px;flex-shrink:0;opacity:0.9;"></i>
                    <span>{$subLabel}</span>
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

    /* ── Sidebar UX polish ── */
    #admin-nav { scrollbar-gutter: stable; }
    .nav-item:not(.active):hover { background: #eef7f0 !important; color: #14532d !important; }
    a.nav-sub { padding: 7px 10px !important; }
    a.nav-sub:hover { background: #f1f5f9 !important; }
    .nav-sub svg, .nav-item svg { stroke: currentColor; }
    .nav-chevron { border-radius: 6px; transition: background 0.15s ease, color 0.15s ease; }
    .nav-chevron:hover { background: rgba(27, 94, 32, 0.08); color: var(--admin-primary); }
    .nav-item:focus-visible, .nav-chevron:focus-visible, .nav-sub:focus-visible,
    #admin-nav-search:focus-visible, #nav-search-clear:focus-visible {
        outline: 2px solid rgba(27, 94, 32, 0.45); outline-offset: 1px;
    }
    #admin-nav-search:focus { border-color: var(--admin-primary); background: #fff; box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.10); }
    #nav-search-clear:hover { background: rgba(15, 23, 42, 0.05); color: #334155; }
    mark.nav-hl { background: #fde68a; color: inherit; padding: 0 1px; border-radius: 2px; }
    .nav-help-btn:hover { border-color: #bbf7d0 !important; background: #f0fdf4 !important; }
    .nav-help-btn svg { transition: transform 0.15s ease; }
    .nav-help-btn:hover svg { transform: scale(1.08); }
    .admin-nav-userrow:hover { background: #f1f5f9 !important; }
</style>
<nav id="admin-nav" style="width:264px;background:#fff;border-right:1px solid rgba(203,213,225,0.7);padding:18px 14px;position:sticky;top:0;height:100vh;display:flex;flex-direction:column;box-shadow:2px 0 16px rgba(15,23,42,0.03);box-sizing:border-box;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(27,94,32,0.15) transparent;flex-shrink:0;">

    <!-- Brand -->
    <div style="display:flex;align-items:center;gap:11px;margin-bottom:28px;padding:0 4px;">
        <img src="/Frontend/images/busia logo.png" alt="Busia Chicken" style="height:44px;width:auto;border-radius:8px;">
        <div>
            <p style="margin:0;font-family:'Outfit',sans-serif;font-size:1.05rem;font-weight:800;color:#0f172a;letter-spacing:-0.3px;">Busia Chicken</p>
            <small style="display:block;color:#64748b;font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Admin Console</small>
        </div>
    </div>

    <!-- Quick search: type what you want to do (works in the drawer too) -->
    <div style="position:relative;margin:0 2px 10px;">
        <i data-lucide="search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none;"></i>
        <input id="admin-nav-search" type="search" placeholder="Find: eggs, feed, money…" autocomplete="off" aria-label="Search navigation"
               style="width:100%;box-sizing:border-box;padding:10px 34px 10px 33px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;font-family:inherit;font-size:0.85rem;color:#0f172a;outline:none;transition:all 0.15s;">
        <button id="nav-search-clear" type="button" aria-label="Clear search" title="Clear search"
                style="display:none;position:absolute;right:5px;top:50%;transform:translateY(-50%);width:24px;height:24px;border:none;background:transparent;color:#94a3b8;cursor:pointer;border-radius:6px;align-items:center;justify-content:center;">
            <i data-lucide="x" style="width:14px;height:14px;"></i>
        </button>
    </div>
    <div id="nav-search-empty" style="display:none;margin:2px 2px 10px;padding:12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;color:#9a3412;font-size:0.8rem;line-height:1.5;">
        Nothing matches here. Try <strong>eggs</strong>, <strong>feed</strong>, <strong>money</strong>, <strong>sick</strong> or <strong>staff</strong>.
    </div>

    <!-- Navigation -->
    <ul id="admin-nav-list" style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:5px;flex-grow:1;">

        <!-- Quick Actions: the 5 things farmers do every day, in plain words -->
        <li style="margin:2px 0 6px;">
            <p style="margin:0 4px 6px;font-size:0.68rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">Today's Tasks</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                <?php
                $quick = [
                    ['production.php',  'clipboard-list', 'Eggs Collected', 'production'],
                    ['extras.php',      'heart-crack',    'Broken Eggs',     'extras'],
                    ['health.php',      'activity',       'Dead / Sick Birds', 'health'],
                    ['feeding.php',     'droplet',        'Feed Used',       'feeding'],
                    ['daily_sales.php', 'dollar-sign',    'Sales Today',     'daily_sales'],
                ];
                $hasQuick = false;
                foreach ($quick as $q) {
                    if (function_exists('busiaCanView') && !busiaCanView($q[3])) continue;
                    $hasQuick = true;
                    $qActive = basename($_SERVER['SCRIPT_NAME']) === $q[0];
                    echo '<a href="/Frontend/admin/' . $q[0] . '" style="display:flex;align-items:center;gap:7px;padding:8px 9px;border-radius:8px;background:' . ($qActive ? 'linear-gradient(135deg,#1B5E20,#2E7D32)' : '#f0fdf4') . ';color:' . ($qActive ? '#fff' : '#166534') . ';text-decoration:none;font-weight:700;font-size:0.76rem;border:1px solid ' . ($qActive ? 'transparent' : '#bbf7d0') . ';transition:all 0.15s;"><i data-lucide="' . $q[1] . '" style="width:15px;height:15px;flex-shrink:0;"></i><span>' . $q[2] . '</span></a>';
                }
                if (!$hasQuick) echo '<div style="grid-column:span 2;color:#94a3b8;font-size:0.75rem;padding:4px;">No daily tasks for your role.</div>';
                ?>
            </div>
        </li>

        <?= navLinkDirect('/Frontend/admin/dashboard.php','layout-dashboard','Dashboard',$isDash) ?>

        <?= navLinkWithSub(
            '/Frontend/admin/hub_mybirds.php?group=flocks',
            'bird',
            'My Birds',
            $isPoultry,
            [
                // 9 old modules combined into 4 clear groups (all tools still one tap away)
                'group_flocks' => ['label' => 'Flocks & Houses',    'href' => '/Frontend/admin/hub_mybirds.php?group=flocks', 'perm' => 'flocks'],
                'group_eggs'   => ['label' => 'Eggs & Losses',      'href' => '/Frontend/admin/hub_mybirds.php?group=eggs',   'perm' => 'production'],
                'group_health' => ['label' => 'Health & Vaccines',  'href' => '/Frontend/admin/hub_mybirds.php?group=health', 'perm' => 'health'],
                'group_growth' => ['label' => 'Growth & Feeding',   'href' => '/Frontend/admin/hub_mybirds.php?group=growth', 'perm' => 'feeding']
            ],
            $tab ?: 'group_flocks'
        ) ?>

        <?= navLinkWithSub(
            '/Frontend/admin/hub_inventory.php',
            'package',
            'Feed & Stores',
            $isInventory,
            [
                'products' => 'Products',
                'stores'   => ['label' => 'Stock', 'href' => '/Frontend/admin/stores.php'],
                'feed'     => ['label' => 'Make Feed', 'href' => '/Frontend/admin/feed_production.php'],
                'eggs'     => ['label' => 'Egg Sizes', 'href' => '/Frontend/admin/egg_grading.php']
            ],
            $tab ?: 'products'
        ) ?>

        <?= navLinkWithSub(
            '/Frontend/admin/hub_money.php?group=overview',
            'trending-up',
            'Money',
            $isSalesFinance,
            [
                // 8 old modules combined into 3 clear groups (all tools still one tap away)
                'money_overview' => ['label' => 'Money Overview',  'href' => '/Frontend/admin/hub_money.php?group=overview', 'perm' => 'hub_finance'],
                'money_income'   => ['label' => 'Sales & Credit',  'href' => '/Frontend/admin/hub_money.php?group=income',   'perm' => 'daily_sales'],
                'money_spending' => ['label' => 'Cash & Spending', 'href' => '/Frontend/admin/hub_money.php?group=spending', 'perm' => 'cashbook']
            ],
            $tab ?: 'money_overview'
        ) ?>

        <?= navLinkWithSub(
            '/Frontend/admin/analytics.php',
            'bar-chart-2',
            'Reports',
            $isReports,
            [
                'analytics' => ['label' => 'Charts & Reports', 'href' => '/Frontend/admin/analytics.php'],
                'import'    => ['label' => 'Import / Export', 'href' => '/Frontend/admin/bulk_import_export.php']
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
                'dropdowns' => 'Lists & Options',
                'settings'  => 'App Settings',
                'logs'      => 'History',
                'permissions' => ['label' => 'User Permissions', 'href' => '/Frontend/admin/permissions.php']
            ],
            $tab ?: 'calendar'
        ) ?>

    </ul>

    <!-- Need a hand? Always reachable, opens the same walkthrough as the ? button -->
    <button id="admin-nav-help" type="button" class="nav-help-btn" onclick="if (typeof openGuideModal === 'function') openGuideModal();"
            style="display:flex;align-items:center;gap:11px;width:100%;text-align:left;margin:6px 0 12px;padding:11px 13px;border:1px solid #d1fae5;border-radius:10px;background:linear-gradient(135deg,#f0fdf4,#ecfdf5);cursor:pointer;transition:all 0.15s;color:#065f46;font-family:inherit;">
        <span style="width:34px;height:34px;border-radius:9px;background:rgba(27,94,32,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i data-lucide="help-circle" style="width:18px;height:18px;"></i></span>
        <span style="flex:1;min-width:0;">
            <span style="display:block;font-weight:700;font-size:0.82rem;">Need a hand?</span>
            <span style="display:block;font-size:0.72rem;color:#15803d;margin-top:1px;">Tap here — see how everything works</span>
        </span>
        <i data-lucide="chevron-right" style="width:15px;height:15px;flex-shrink:0;opacity:0.7;"></i>
    </button>

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
