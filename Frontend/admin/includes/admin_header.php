<?php
/**
 * Admin page header and left navigation.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../includes/config.php';
}

if (!isset($page_title)) $page_title = 'Admin Console';
// Admin access check (Basic authentication for ANY admin area)
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager', 'stock_manager', 'sales_staff'], true)) {
    // Redirect to login if not authorized
    header('Location: /busiaadmin');
    exit;
}

// Authorization logic for specific roles
$isAdmin = in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true);

/* ── Role-based module permissions ────────────────────────────────
   The role_permissions matrix is seeded automatically (see
   auto_migrate.php). super_admin is always allowed; every other role
   is checked against the matrix for the module they are opening, and
   the sidebar hides entries the role cannot view. */
$busia_current_module = function_exists('busiaModuleKeyForScript') ? busiaModuleKeyForScript(basename($_SERVER['SCRIPT_NAME'])) : '';
$busia_perms = function_exists('busiaRolePermissions') ? busiaRolePermissions(null) : [];
$busia_role_perms = $busia_perms[$_SESSION['role'] ?? ''] ?? [];
$GLOBALS['_busia_role_perms'] = $busia_role_perms;
if (!function_exists('busiaCanView')) {
    function busiaCanView(string $module): bool {
        if (($_SESSION['role'] ?? '') === 'super_admin') return true;
        $perms = $GLOBALS['_busia_role_perms'] ?? [];
        return (bool)($perms[$module]['view'] ?? 0);
    }
    function busiaCanEdit(string $module): bool {
        if (($_SESSION['role'] ?? '') === 'super_admin') return true;
        $perms = $GLOBALS['_busia_role_perms'] ?? [];
        return (bool)($perms[$module]['edit'] ?? 0);
    }
}
// Block modules the role has no view permission for.
if ($busia_current_module !== '' && $busia_current_module !== 'dashboard' && !busiaCanView($busia_current_module)) {
    header('Location: /Frontend/admin/dashboard.php?denied=1');
    exit;
}

/* ── Quick Actions dropdown (per-page shortcuts) ──────────────────
   Every admin page gets a "Quick Actions" menu in the top bar with
   shortcuts to that page's main add/edit forms. Pages may override by
   setting $quickActions before including this header. */
if (!function_exists('busiaDefaultQuickActions')) {
    function busiaDefaultQuickActions(string $script): array {
        $map = [
            'dashboard.php' => [
                ['label' => 'New Customer Order', 'icon' => 'shopping-bag', 'href' => '/Frontend/admin/orders.php'],
                ['label' => 'Analytics & Charts', 'icon' => 'bar-chart-2', 'href' => '/Frontend/admin/analytics.php'],
                ['label' => 'Bulk Import / Export', 'icon' => 'upload', 'href' => '/Frontend/admin/bulk_import_export.php'],
                ['label' => 'LPO & Invoicing', 'icon' => 'file-text', 'href' => '/Frontend/admin/lpo.php'],
            ],
            'products.php' => [
                ['label' => 'Add Product', 'icon' => 'package-plus', 'click' => 'Add Product', 'href' => '/Frontend/admin/products.php'],
                ['label' => 'Export Products CSV', 'icon' => 'download', 'href' => '/Backend/api/export.php?module=products'],
            ],
            'orders.php' => [['label' => 'New Order', 'icon' => 'plus-circle', 'click' => 'New Order', 'href' => '/Frontend/admin/orders.php']],
            'flocks.php' => [['label' => 'Hatch New Flock', 'icon' => 'plus', 'click' => 'Hatch New Flock', 'href' => '/Frontend/admin/flocks.php']],
            'production.php' => [['label' => 'Log Daily Yield', 'icon' => 'plus', 'click' => 'Log Daily Yield', 'href' => '/Frontend/admin/production.php']],
            'vaccinations.php' => [['label' => 'Schedule Vaccine', 'icon' => 'plus', 'click' => 'Schedule Vaccine', 'href' => '/Frontend/admin/vaccinations.php']],
            'batches.php' => [
                ['label' => 'New Batch', 'icon' => 'plus', 'click' => 'New Batch', 'href' => '/Frontend/admin/batches.php'],
                ['label' => 'Log Today\'s Record', 'icon' => 'clipboard', 'click' => 'Log Today\'s Record', 'href' => '/Frontend/admin/batches.php'],
            ],
            'health.php' => [['label' => 'New Health Record', 'icon' => 'plus', 'click' => 'Add Health Record', 'href' => '/Frontend/admin/health.php']],
            'broiler.php' => [['label' => 'Record Weigh-In', 'icon' => 'plus', 'click' => 'Record Weigh-In', 'href' => '/Frontend/admin/broiler.php']],
            'hatchery.php' => [['label' => 'New Hatch Record', 'icon' => 'plus', 'click' => 'New Hatch Record', 'href' => '/Frontend/admin/hatchery.php']],
            'feeding.php' => [['label' => 'Record Feeding', 'icon' => 'plus', 'click' => 'Record Feeding', 'href' => '/Frontend/admin/feeding.php']],
            'extras.php' => [
                ['label' => 'Record Egg Loss', 'icon' => 'alert-circle', 'click' => 'Record Loss', 'href' => '/Frontend/admin/extras.php?tab=losses'],
                ['label' => 'New Quality Test', 'icon' => 'flask-conical', 'click' => 'New Test', 'href' => '/Frontend/admin/extras.php?tab=quality'],
            ],
            'egg_grading.php' => [['label' => 'New Grading', 'icon' => 'plus', 'click' => 'New Grading', 'href' => '/Frontend/admin/egg_grading.php']],
            'stores.php' => [['label' => 'Record Movement', 'icon' => 'arrow-down-circle', 'click' => 'Record Movement', 'href' => '/Frontend/admin/stores.php']],
            'feed_production.php' => [['label' => 'Produce Feed', 'icon' => 'plus', 'click' => 'Produce Feed', 'href' => '/Frontend/admin/feed_production.php']],
            'lpo.php' => [['label' => 'New LPO / Quotation / Invoice', 'icon' => 'plus', 'click' => 'New Document', 'href' => '/Frontend/admin/lpo.php']],
            'credit.php' => [['label' => 'Record Credit Sale', 'icon' => 'plus', 'click' => 'Record Credit Sale', 'href' => '/Frontend/admin/credit.php']],
            'bulk_sales.php' => [['label' => 'New Bulk Sale', 'icon' => 'plus', 'click' => 'New Sale', 'href' => '/Frontend/admin/bulk_sales.php']],
            'profit.php' => [['label' => 'Add Cost', 'icon' => 'plus', 'click' => 'Add Cost', 'href' => '/Frontend/admin/profit.php']],
            'daily_sales.php' => [['label' => 'Record Daily Sales', 'icon' => 'plus', 'click' => 'Record Daily Sales', 'href' => '/Frontend/admin/daily_sales.php']],
            'purchase_orders.php' => [['label' => 'New Purchase Order', 'icon' => 'plus', 'click' => 'New Order', 'href' => '/Frontend/admin/purchase_orders.php']],
            'staff.php' => [['label' => 'Add Staff', 'icon' => 'user-plus', 'href' => '/Frontend/admin/staff.php?action=add']],
            'users.php' => [['label' => 'Create User Account', 'icon' => 'user-plus', 'click' => 'Add User', 'href' => '/Frontend/admin/users.php']],
            'hub_settings.php' => [
                ['label' => 'System Dropdowns', 'icon' => 'list', 'href' => '/Frontend/admin/dropdowns.php'],
                ['label' => 'Permissions & Roles', 'icon' => 'shield', 'href' => '/Frontend/admin/permissions.php'],
                ['label' => 'Activity Logs', 'icon' => 'history', 'href' => '/Frontend/admin/logs.php'],
            ],
            'calendar.php' => [['label' => 'Add Calendar Event', 'icon' => 'plus', 'click' => 'Add Event', 'href' => '/Frontend/admin/calendar.php']],
            'dropdowns.php' => [['label' => 'Add Dropdown Option', 'icon' => 'plus', 'click' => 'Add Option', 'href' => '/Frontend/admin/dropdowns.php']],
            'logs.php' => [['label' => 'View Activity Logs', 'icon' => 'history', 'href' => '/Frontend/admin/logs.php']],
            'permissions.php' => [['label' => 'Edit Roles & Permissions', 'icon' => 'shield', 'href' => '/Frontend/admin/permissions.php']],
            // Hub pages — shortcut to the hub's main sub-modules
            'hub_finance.php' => [
                ['label' => 'Cashbook', 'icon' => 'book', 'href' => '/Frontend/admin/cashbook.php'],
                ['label' => 'LPO & Invoicing', 'icon' => 'file-text', 'href' => '/Frontend/admin/lpo.php'],
                ['label' => 'Customer Credit', 'icon' => 'credit-card', 'href' => '/Frontend/admin/credit.php'],
                ['label' => 'Bulk Sales', 'icon' => 'shopping-cart', 'href' => '/Frontend/admin/bulk_sales.php'],
            ],
            'hub_inventory.php' => [
                ['label' => 'Products Catalog', 'icon' => 'package', 'href' => '/Frontend/admin/products.php'],
                ['label' => 'Stores & Stock', 'icon' => 'warehouse', 'href' => '/Frontend/admin/stores.php'],
                ['label' => 'Egg Grading', 'icon' => 'egg', 'href' => '/Frontend/admin/egg_grading.php'],
            ],
            'hub_operations.php' => [
                ['label' => 'Hatch New Flock', 'icon' => 'plus', 'click' => 'Add Flock', 'href' => '/Frontend/admin/hub_operations.php'],
                ['label' => 'Log Today\'s Production', 'icon' => 'clipboard', 'click' => 'Log Today\'s Production', 'href' => '/Frontend/admin/hub_operations.php'],
                ['label' => 'Flocks', 'icon' => 'bird', 'href' => '/Frontend/admin/flocks.php'],
            ],
            'hub_people.php' => [
                ['label' => 'Add Staff Member', 'icon' => 'user-plus', 'click' => 'Add Staff Member', 'href' => '/Frontend/admin/hub_people.php'],
                ['label' => 'Tasks', 'icon' => 'check-square', 'href' => '/Frontend/admin/tasks.php'],
                ['label' => 'Messages', 'icon' => 'message-square', 'href' => '/Frontend/admin/messages.php'],
            ],
            'bulk_import_export.php' => [
                ['label' => 'Export Products CSV', 'icon' => 'download', 'href' => '/Backend/api/export.php?module=products'],
                ['label' => 'Export Raw Materials CSV', 'icon' => 'download', 'href' => '/Backend/api/export.php?module=raw_materials'],
                ['label' => 'Export Flocks CSV', 'icon' => 'download', 'href' => '/Backend/api/export.php?module=flocks'],
                ['label' => 'Export LPO Documents CSV', 'icon' => 'download', 'href' => '/Backend/api/export.php?module=lpo_documents'],
            ],
            'sales.php' => [['label' => 'Sales & Finance Hub', 'icon' => 'trending-up', 'href' => '/Frontend/admin/hub_finance.php']],
            'payments.php' => [['label' => 'Sales & Finance Hub', 'icon' => 'trending-up', 'href' => '/Frontend/admin/hub_finance.php']],
            'expenses.php' => [['label' => 'Sales & Finance Hub', 'icon' => 'trending-up', 'href' => '/Frontend/admin/hub_finance.php']],
            'reports.php' => [['label' => 'Analytics & Charts', 'icon' => 'bar-chart-2', 'href' => '/Frontend/admin/analytics.php']],
            'orders.php' => [['label' => 'Sales & Finance Hub', 'icon' => 'trending-up', 'href' => '/Frontend/admin/hub_finance.php']],
            'operations.php' => [['label' => 'Poultry Operations Hub', 'icon' => 'bird', 'href' => '/Frontend/admin/hub_operations.php']],
            'incoming_stock.php' => [['label' => 'Stores & Stock', 'icon' => 'warehouse', 'href' => '/Frontend/admin/stores.php']],
            'settings.php' => [['label' => 'App Settings Hub', 'icon' => 'settings', 'href' => '/Frontend/admin/hub_settings.php']],
            'messages.php' => [['label' => 'Team & Messages Hub', 'icon' => 'users', 'href' => '/Frontend/admin/hub_people.php']],
            'tasks.php' => [['label' => 'Assign Task', 'icon' => 'plus', 'click' => 'Assign Task', 'href' => '/Frontend/admin/tasks.php']],
        ];
        return $map[$script] ?? [];
    }
}
if (!isset($quickActions)) {
    $quickActions = busiaDefaultQuickActions(basename($_SERVER['SCRIPT_NAME']));
}

$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/vendor/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="icon" type="image/png" href="/Frontend/images/busia logo.png">
    <style>
        :root {
            --admin-primary: #1B5E20;
            --admin-primary-light: #2E7D32;
            --admin-accent: #FFC107;
            --admin-dark: #0f172a;
            --admin-sidebar-bg: #ffffff;
            --admin-body-bg: #f8fafc;
            --admin-border: rgba(203, 213, 225, 0.8);
            --admin-card-bg: #ffffff;
            --admin-text-main: #1e293b;
            --admin-text-heading: #0f172a;
        }

        body.admin-layout { 
            background: var(--admin-body-bg); 
            color: var(--admin-text-main);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }

        nav.navbar { display: none !important; }
        
        .admin-shell { 
            display: flex; 
            min-height: 100vh; 
        }

        /* Sidebar Styling */
        .admin-sidebar { 
            width: 280px; 
            background: var(--admin-sidebar-bg); 
            border-right: 1px solid var(--admin-border); 
            padding: 20px 16px; 
            position: sticky; 
            top: 0; 
            height: 100vh;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.02); 
            box-sizing: border-box;
            z-index: 100;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(27, 94, 32, 0.2) transparent;
        }

        .admin-sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(27, 94, 32, 0.2);
            border-radius: 4px;
        }

        .admin-sidebar-brand { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            margin-bottom: 24px; 
            padding: 0 8px;
        }

        .admin-sidebar-brand p { 
            margin: 0; 
            font-family: 'Outfit', sans-serif;
            font-size: 1.15rem; 
            font-weight: 800; 
            color: var(--admin-text-heading);
            letter-spacing: -0.5px;
        }

        .admin-sidebar-brand small { 
            display: block; 
            color: #475569; 
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .admin-sidebar-nav { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
            display: flex;
            flex-direction: column;
            gap: 6px; 
            flex-grow: 1;
        }

        .admin-sidebar-nav a { 
            display: flex; 
            align-items: center;
            gap: 12px;
            padding: 10px 14px; 
            border-radius: 4px; 
            color: #475569; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 0.95rem;
            border: 1px solid transparent; 
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); 
        }

        .admin-sidebar-nav a i {
            width: 20px;
            height: 20px;
            transition: transform 0.2s ease;
        }

        .admin-sidebar-nav a:hover { 
            color: var(--admin-primary);
            background: rgba(27, 94, 32, 0.04);
        }

        .admin-sidebar-nav a.active { 
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-light) 100%); 
            color: #ffffff; 
            box-shadow: 0 4px 12px rgba(27, 94, 32, 0.15);
        }

        .admin-sidebar-nav a.active i {
            transform: scale(1.05);
        }

        /* Sidebar Dropdown Styling */
        .admin-sidebar-nav .dropdown-trigger {
            cursor: pointer;
            justify-content: space-between !important;
        }

        .admin-sidebar-nav .sidebar-dropdown {
            list-style: none;
            padding: 0;
            margin: 0;
            display: none;
            flex-direction: column;
            gap: 2px;
            padding-left: 20px;
            margin-top: 4px;
            margin-bottom: 10px;
            border-left: 2px solid var(--admin-border);
            margin-left: 24px;
        }

        .admin-sidebar-nav .sidebar-dropdown.open {
            display: flex;
        }

        .admin-sidebar-nav .dropdown-trigger .chevron {
            width: 16px;
            height: 16px;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .admin-sidebar-nav .dropdown-trigger.open .chevron {
            transform: rotate(180deg);
        }

        .admin-sidebar-nav .sidebar-dropdown a {
            font-size: 0.88rem;
            padding: 8px 14px;
            font-weight: 500;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .admin-sidebar-nav .sidebar-dropdown a i {
            width: 16px;
            height: 16px;
            opacity: 0.7;
            transition: all 0.2s ease;
        }

        .admin-sidebar-nav .sidebar-dropdown a:hover {
            color: var(--admin-primary);
            background: rgba(27, 94, 32, 0.04);
            text-decoration: none;
        }

        .admin-sidebar-nav .sidebar-dropdown a:hover i {
            opacity: 1;
            transform: translateX(2px);
        }

        .admin-sidebar-nav .sidebar-dropdown a.active {
            background: rgba(27, 94, 32, 0.08);
            color: var(--admin-primary);
            font-weight: 700;
            box-shadow: none;
        }

        .admin-sidebar-nav .sidebar-dropdown a.active i {
            opacity: 1;
            color: var(--admin-primary);
        }

        .admin-sidebar-footer { 
            margin-top: auto; 
            padding-top: 14px;
            border-top: 1px solid var(--admin-border);
        }

        .admin-sidebar-footer .btn { 
            width: 100%; 
            justify-content: center; 
            border-radius: 4px;
        }

        /* Content Area */
        .admin-content { 
            flex: 1; 
            padding: 24px; 
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
            min-width: 0; /* let the content column shrink beside the fixed sidebar */
        }

        /* Top utility bar */
        .admin-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: var(--admin-card-bg);
            border: 1px solid var(--admin-border);
            border-radius: 4px;
            padding: 12px 20px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
        }

        .admin-top-bar .welcome-message h2 {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            color: var(--admin-text-heading);
        }

        .admin-top-bar .welcome-message p {
            margin: 2px 0 0 0;
            font-size: 0.85rem;
            color: #475569;
        }

        .admin-profile-badge {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 4px;
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-accent) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
        }

        /* Dashboard Cards & Layout */
        .admin-card { 
            background: var(--admin-card-bg); 
            border: 1px solid var(--admin-border); 
            border-radius: 4px; 
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.03); 
            padding: 20px;
            box-sizing: border-box;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .admin-card:hover {
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
        }

        .dashboard-hero { 
            display: flex; 
            justify-content: space-between; 
            gap: 20px; 
            align-items: flex-start; 
            margin-bottom: 26px; 
        }

        .dashboard-hero .hero-text h1 { 
            margin: 0; 
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem; 
            font-weight: 700;
            color: var(--admin-text-heading);
            letter-spacing: -0.5px;
        }

        .dashboard-hero .hero-text p { 
            color: #64748b; 
            margin-top: 8px; 
            line-height: 1.6; 
        }

        .hero-pill { 
            display: inline-flex; 
            gap: 8px; 
            align-items: center; 
            background: rgba(27, 94, 32, 0.06); 
            color: var(--admin-primary); 
            padding: 8px 16px; 
            border-radius: 4px; 
            font-weight: 600; 
            font-size: 0.85rem;
        }

        .stat-grid { 
            display: grid; 
            grid-template-columns: repeat(3, minmax(0, 1fr)); 
            gap: 20px; 
            margin-bottom: 32px;
        }

        .stat-card { 
            padding: 24px; 
            border-radius: 4px; 
            background: var(--admin-card-bg); 
            border: 1px solid var(--admin-border); 
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.03); 
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card-info {
            display: flex;
            flex-direction: column;
        }

        .stat-card small { 
            color: #64748b; 
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
        }

        .stat-card strong { 
            display: block; 
            margin-top: 8px; 
            font-size: 2rem; 
            color: var(--admin-text-heading); 
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }

        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 4px;
            background: rgba(27, 94, 32, 0.06);
            color: var(--admin-primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card-icon.accent {
            background: rgba(255, 193, 7, 0.1);
            color: #d97706;
        }

        .stat-card-icon.info {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .admin-table th {
            padding: 16px 20px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--admin-border);
            background: var(--admin-body-bg);
        }

        .admin-table td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--admin-border);
            font-size: 0.95rem;
            color: var(--admin-text-main);
        }

        .admin-table tr:hover td {
            background: rgba(248, 250, 252, 0.6);
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 2px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge-pill-success {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-pill-warning {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-pill-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* Form elements */
        .admin-form-group {
            margin-bottom: 20px;
        }

        .admin-form-label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--admin-text-heading);
            margin-bottom: 6px;
        }

        .admin-form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            box-sizing: border-box;
        }

        .admin-form-control:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.15);
        }

        .admin-actions { display: flex; flex-wrap: wrap; gap: 12px; }

        /* ═══════════════════════════════════════════════════════════════ */
        /* ADMIN BUTTON SYSTEM — overrides global .btn for admin context   */
        /* ═══════════════════════════════════════════════════════════════ */

        /* Base admin button reset — tighter padding than front-end */
        .admin-layout .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
            border: 1px solid transparent;
            text-decoration: none;
            white-space: nowrap;
            line-height: 1;
        }

        .admin-layout .btn i,
        .admin-layout .btn svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        /* Small variant used for table row actions */
        .admin-layout .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            gap: 5px;
            border-radius: 5px;
        }

        .admin-layout .btn-sm i,
        .admin-layout .btn-sm svg {
            width: 14px;
            height: 14px;
        }

        /* Primary — green */
        .admin-layout .btn-primary {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-light) 100%);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 2px 8px rgba(27,94,32,0.2);
        }

        .admin-layout .btn-primary:hover {
            background: linear-gradient(135deg, #145214 0%, var(--admin-primary) 100%);
            box-shadow: 0 4px 16px rgba(27,94,32,0.3);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .admin-layout .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 1px 4px rgba(27,94,32,0.2);
        }

        /* Outline — border only */
        .admin-layout .btn-outline {
            background: transparent;
            border: 1.5px solid var(--admin-border);
            color: #475569;
        }

        .admin-layout .btn-outline:hover {
            background: rgba(27,94,32,0.06);
            border-color: var(--admin-primary);
            color: var(--admin-primary);
            transform: translateY(-1px);
        }

        /* Trans (transparent ghost) — for table row secondary actions */
        .admin-layout .btn-trans {
            background: rgba(241,245,249,0.8);
            border: 1.5px solid #e2e8f0;
            color: #475569;
        }

        .admin-layout .btn-trans:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #1e293b;
            transform: translateY(-1px);
        }

        /* Danger variant */
        .admin-layout .btn-danger {
            background: #fee2e2;
            border: 1.5px solid #fecaca;
            color: #b91c1c;
        }

        .admin-layout .btn-danger:hover {
            background: #fca5a5;
            border-color: #f87171;
            color: #7f1d1d;
            transform: translateY(-1px);
        }

        /* Warning variant */
        .admin-layout .btn-warning {
            background: #fef3c7;
            border: 1.5px solid #fde68a;
            color: #b45309;
        }

        .admin-layout .btn-warning:hover {
            background: #fde68a;
            border-color: #fbbf24;
            color: #78350f;
            transform: translateY(-1px);
        }

        /* Info variant */
        .admin-layout .btn-info {
            background: #dbeafe;
            border: 1.5px solid #bfdbfe;
            color: #1d4ed8;
        }

        .admin-layout .btn-info:hover {
            background: #bfdbfe;
            border-color: #93c5fd;
            color: #1e3a8a;
            transform: translateY(-1px);
        }

        /* ═══════════════════════════════════════════
           Table action button group (View/Edit/Delete)
        ═══════════════════════════════════════════ */
        .tbl-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            justify-content: flex-end;
        }

        /* Auto-icons for semantic btn-sm links in tables via data attrs */
        .admin-layout a.btn-sm[href*="action=view"],
        .admin-layout a.btn-sm[href*="action=edit"],
        .admin-layout button.btn-sm[onclick*="edit"],
        .admin-layout button.btn-sm[onclick*="delete"] {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* ═══════════════════════════════════════════
           RESPONSIVE — collapsible sidebar drawer
        ═══════════════════════════════════════════ */
        .admin-nav-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 1px solid var(--admin-border);
            border-radius: 8px;
            background: #fff;
            color: var(--admin-primary);
            cursor: pointer;
            flex-shrink: 0;
        }
        .admin-nav-toggle:hover {
            background: rgba(27, 94, 32, 0.06);
            border-color: var(--admin-primary);
        }
        .admin-nav-backdrop {
            display: none;
        }

        /* Tablet and below: sidebar becomes a slide-in drawer */
        @media (max-width: 1023px) {
            .admin-nav-toggle { display: inline-flex; }

            .admin-nav-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.25s ease;
                z-index: 1090;
            }
            body.nav-open .admin-nav-backdrop { opacity: 1; pointer-events: auto; }
            body.nav-open { overflow: hidden; }

            #admin-nav {
                position: fixed !important;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-105%);
                transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1100;
                box-shadow: 4px 0 28px rgba(15, 23, 42, 0.18);
            }
            #admin-nav.open { transform: translateX(0); }

            .admin-content { padding: 14px; }

            /* Collapse the common inline card grids so nothing clips on tablets */
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            div[style*="repeat(4,1fr)"] { grid-template-columns: repeat(2, 1fr) !important; }
            div[style*="repeat(3,1fr)"] { grid-template-columns: repeat(2, 1fr) !important; }
        }

        /* Phones: single-column everything, stack the top bar */
        @media (max-width: 640px) {
            .admin-content { padding: 12px; }
            .admin-top-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            .admin-top-bar > div:last-child { justify-content: flex-end; }
            .dashboard-hero { flex-direction: column; }
            .stat-grid { grid-template-columns: 1fr !important; }
            div[style*="repeat(4,1fr)"],
            div[style*="repeat(3,1fr)"],
            div[style*="repeat(2,1fr)"],
            div[style*="2fr 1fr"] { grid-template-columns: 1fr !important; }
        }
    </style>
    <script>
        // Dynamically hide sidebar and topbar elements if loaded inside an iframe
        if (window.self !== window.top) {
            document.documentElement.classList.add('in-iframe');
            const style = document.createElement('style');
            style.textContent = '.admin-shell > nav, .admin-top-bar { display: none !important; } .admin-shell { display: block !important; } .admin-content { padding: 0 !important; }';
            document.head.appendChild(style);
        }
    </script>
</head>
<body class="admin-layout">
<script>
    window.BusiaAdmin = window.BusiaAdmin || {};
    window.BusiaAdmin.csrfToken = <?php echo json_encode($csrf_token); ?>;
</script>
<div class="admin-shell">
    <div id="admin-nav-backdrop" class="admin-nav-backdrop"></div>
    <?php include __DIR__ . '/admin_sidebar.php'; ?>
    <div class="admin-content">
        <!-- Top utility bar -->
        <div class="admin-top-bar">
            <button id="admin-nav-toggle" class="admin-nav-toggle" aria-label="Open menu" title="Menu">
                <i data-lucide="menu" style="width: 22px; height: 22px;"></i>
            </button>
            <div class="welcome-message">
                <h2>Hello, <?php echo htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Admin'); ?></h2>
                <p>Welcome back to your dashboard portal.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <?php if (!empty($quickActions)): ?>
                <div class="quick-actions-wrap" style="position: relative;">
                    <button id="quick-actions-toggle" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; font-size: 0.85rem; border-radius: 8px;">
                        <i data-lucide="zap" style="width: 15px; height: 15px;"></i> Quick Actions
                        <i data-lucide="chevron-down" style="width: 13px; height: 13px; transition: transform 0.2s ease;"></i>
                    </button>
                    <div id="quick-actions-menu" style="display: none; position: absolute; right: 0; top: calc(100% + 8px); min-width: 250px; background: #fff; border: 1px solid var(--admin-border); border-radius: 10px; box-shadow: 0 16px 40px rgba(15,23,42,0.14); padding: 7px; z-index: 1300;">
                        <div style="padding: 8px 12px 6px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">This page</div>
                        <?php foreach ($quickActions as $qa): ?>
                            <?php if (isset($qa['href']) && isset($qa['click'])): ?>
                                <a href="<?= htmlspecialchars($qa['href'], ENT_QUOTES, 'UTF-8') ?>" data-quick-click="<?= htmlspecialchars($qa['click'], ENT_QUOTES, 'UTF-8') ?>" style="display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 7px; text-decoration: none; color: #1e293b; font-size: 0.88rem; font-weight: 600; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                    <i data-lucide="<?= htmlspecialchars($qa['icon'] ?? 'arrow-right', ENT_QUOTES, 'UTF-8') ?>" style="width: 16px; height: 16px; color: var(--admin-primary); flex-shrink: 0;"></i>
                                    <?= htmlspecialchars($qa['label'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php elseif (isset($qa['href'])): ?>
                                <a href="<?= htmlspecialchars($qa['href'], ENT_QUOTES, 'UTF-8') ?>" style="display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 7px; text-decoration: none; color: #1e293b; font-size: 0.88rem; font-weight: 600; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                    <i data-lucide="<?= htmlspecialchars($qa['icon'] ?? 'arrow-right', ENT_QUOTES, 'UTF-8') ?>" style="width: 16px; height: 16px; color: var(--admin-primary); flex-shrink: 0;"></i>
                                    <?= htmlspecialchars($qa['label'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php else: ?>
                                <button type="button" data-quick-click="<?= htmlspecialchars($qa['click'] ?? '', ENT_QUOTES, 'UTF-8') ?>" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 9px 12px; border: none; background: none; border-radius: 7px; color: #1e293b; font-size: 0.88rem; font-weight: 600; text-align: left; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                    <i data-lucide="<?= htmlspecialchars($qa['icon'] ?? 'arrow-right', ENT_QUOTES, 'UTF-8') ?>" style="width: 16px; height: 16px; color: var(--admin-primary); flex-shrink: 0;"></i>
                                    <?= htmlspecialchars($qa['label'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <button id="open-system-guide" title="System Walkthrough Guide" style="background: none; border: none; cursor: pointer; color: var(--admin-primary); display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; background: rgba(27, 94, 32, 0.08); transition: all 0.2s; outline: none;" onmouseover="this.style.background='rgba(27, 94, 32, 0.15)'" onmouseout="this.style.background='rgba(27, 94, 32, 0.08)'">
                    <i data-lucide="help-circle" style="width: 22px; height: 22px; stroke-width: 2.2;"></i>
                </button>
                <div class="admin-profile-badge">
                    <div class="admin-avatar">
                        <?php 
                        $initial = strtoupper(substr($_SESSION['first_name'] ?? $_SESSION['username'] ?? 'A', 0, 1));
                        echo $initial;
                        ?>
                    </div>
                    <div style="text-align: left;">
                        <h5 style="margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--admin-text-heading);"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Administrator'); ?></h5>
                        <span class="badge-pill badge-pill-success" style="padding: 2px 8px; font-size: 0.7rem; margin-top: 2px; display: inline-block;"><?php echo htmlspecialchars(str_replace('_', ' ', $_SESSION['role'] ?? 'super_admin')); ?></span>
                    </div>
                </div>
            </div>
        </div>
