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
// Admin access check (Basic authentication for ANY admin area)
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager', 'stock_manager'], true)) {
    // Redirect to login if not authorized
    header('Location: /busiaadmin');
    exit;
}

// Authorization logic for specific roles
$isAdmin = in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true);
$isStockManager = ($_SESSION['role'] ?? '') === 'stock_manager';

// Restrict Stock Manager to ONLY stock-related pages
if ($isStockManager) {
    $currentPage = basename($_SERVER['SCRIPT_NAME']);
    $allowedStockPages = ['stock_dashboard.php', 'stock_calculator.php', 'stock_recipes.php', 'stock_costing.php', 'stock_alerts.php', 'incoming_stock.php', 'profile.php'];
    if (!in_array($currentPage, $allowedStockPages) && strpos($currentPage, 'stock_') === false && strpos($currentPage, 'incoming_') === false) {
        // Redirect stock managers away from non-stock pages (like orders, settings, users)
        header('Location: /Frontend/admin/stock_dashboard.php');
        exit;
    }
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
    </style>
</head>
<body class="admin-layout">
<script>
    window.BusiaAdmin = window.BusiaAdmin || {};
    window.BusiaAdmin.csrfToken = <?php echo json_encode($csrf_token); ?>;
</script>
<div class="admin-shell">
    <?php include __DIR__ . '/admin_sidebar.php'; ?>
    <div class="admin-content">
        <!-- Top utility bar -->
    <?php include __DIR__ . '/admin_sidebar.php'; ?>
    <div class="admin-content">
        <!-- Top utility bar -->
        <div class="admin-top-bar">
            <div class="welcome-message">
                <h2>Hello, <?php echo htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Admin'); ?></h2>
                <p>Welcome back to your dashboard portal.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
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
