<?php
/**
 * Global Header & Navigation - Premium minimal redesign
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $temp_dir = sys_get_temp_dir();
    if (is_writable($temp_dir)) {
        session_save_path($temp_dir);
    }
    session_start();
}

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}

if (!isset($page_title)) {
    $page_title = SITE_NAME . ' - ' . SITE_TAGLINE;
}

$currentPage = basename($_SERVER['REQUEST_URI'] ?? '', '.php');
$currentPage = rtrim($currentPage, '/');
if ($currentPage === '' || $currentPage === 'index') {
    $currentPage = 'home';
}

function navActive(string $page, string $current): string {
    return ($page === $current) ? ' active' : '';
}

/**
 * Cache-busting: append a version derived from the file's mtime so that
 * phones and browsers never serve stale CSS/JS after a deploy.
 */
function assetVer(string $path): string {
    $full = __DIR__ . '/../' . ltrim($path, '/');
    $mtime = @filemtime($full);
    return '?v=' . ($mtime ?: '1');
}

function optimizedSiteImage(string $url): string
{
    $path = parse_url($url, PHP_URL_PATH);
    if (!is_string($path) || !str_starts_with($path, '/Frontend/images/')) return $url;
    $stem = pathinfo(rawurldecode(basename($path)), PATHINFO_FILENAME);
    $name = strtolower(str_replace(' ', '-', $stem)) . '.webp';
    return is_file(__DIR__ . '/../images/' . $name) ? '/Frontend/images/' . rawurlencode($name) : $url;
}

if (!function_exists('optimizedShopImage')) {
    function optimizedShopImage(string $url): string { return optimizedSiteImage($url); }
}

// Determine login state for public site (only customer role shows on website)
$is_customer_logged_in = !empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'customer';

// Homepage gets a full-bleed hero, so the nav starts transparent over it
$is_home = ($currentPage === 'home');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>

    <!-- DNS Prefetch & Preconnect for external resources -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts: Non-render-blocking async load. Outfit + Inter only (dropped Fraunces to save a request) -->
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap"></noscript>

    <!-- Preload critical above-fold assets -->
    <?php if ($is_home): ?><link rel="preload" href="/Frontend/images/adbg.webp" as="image" type="image/webp" fetchpriority="high"><?php endif; ?>
    <link rel="preload" href="<?php echo BASE_URL; ?>assets/css/premium.css<?php echo assetVer('assets/css/premium.css'); ?>" as="style">

    <!-- PWA & Mobile Capabilities -->
    <meta name="theme-color" content="#0F472B">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Busia Chicken">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/Frontend/images/icon-192.png">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/vendor/swiper/swiper-bundle.min.css<?php echo assetVer('assets/vendor/swiper/swiper-bundle.min.css'); ?>">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css<?php echo assetVer('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components.css<?php echo assetVer('assets/css/components.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/animations.css<?php echo assetVer('assets/css/animations.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/responsive.css<?php echo assetVer('assets/css/responsive.css'); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/premium.css<?php echo assetVer('assets/css/premium.css'); ?>">

    <!-- Favicon & Logos -->
    <link rel="icon" type="image/png" href="/Frontend/images/favicon.png">
    <link rel="alternate icon" href="/Frontend/images/favicon.png">

    <!-- Schema Markup -->
    <?php
    if (file_exists(__DIR__ . '/seo.php')) {
        require_once __DIR__ . '/seo.php';
        if (function_exists('generateOrganizationSchema') && function_exists('generateLocalBusinessSchema')) {
            echo '<script type="application/ld+json">' . generateOrganizationSchema() . '</script>' . "\n";
            echo '<script type="application/ld+json">' . generateLocalBusinessSchema() . '</script>' . "\n";
        }
    }
    ?>
    <?php
    $announcement = '🐣 Fresh Day-Old Chicks & Grade-A Farm Eggs Available Daily in Nasira & Tangakona! Call +254 727 585 599';
    $show_announcement = '1';
    if (function_exists('getDB') && ($pdo = ($GLOBALS['pdo'] ?? null)) instanceof PDO) {
        $announcement = function_exists('getSetting') ? getSetting('announcement_bar', $announcement) : $announcement;
        $show_announcement = function_exists('getSetting') ? getSetting('show_announcement', '1') : '1';
    }
    $has_announcement = ($show_announcement === '1' && !empty($announcement));
    ?>
</head>
<body class="<?php echo $is_home ? 'has-hero' : ''; ?><?php echo $has_announcement ? ' has-announcement' : ''; ?>">

    <!-- Scroll progress bar -->
    <div class="scroll-progress" id="scroll-progress"></div>

    <!-- Unified Site Header Wrapper (Prevents Any Bar Overlap) -->
    <header class="site-header" id="site-header">
        <?php if ($has_announcement): ?>
        <div class="top-announcement-bar">
            <div class="container announcement-inner">
                <span class="announcement-pill">Live Farm Update</span>
                <span class="announcement-text"><?php echo htmlspecialchars($announcement, ENT_QUOTES, 'UTF-8'); ?></span>
                <div class="announcement-locations">
                    <a href="/Frontend/pages/contact.php#nasira" class="ann-loc"><i data-lucide="map-pin" style="width:12px;height:12px;"></i> Nasira AC HQ</a>
                    <span class="sep">•</span>
                    <a href="/Frontend/pages/contact.php#tangakona" class="ann-loc"><i data-lucide="store" style="width:12px;height:12px;"></i> Tangakona Depot</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Main Navigation -->
        <nav class="navbar premium-nav" id="site-nav">
        <div class="container nav-inner">
            <!-- Brand Logo with Slogan -->
            <a href="/" class="nav-brand" aria-label="Busia Chicken Limited - The Chicken People">
                <picture>
                    <source srcset="/Frontend/images/logo-sm.webp" type="image/webp">
                    <img src="/Frontend/images/logo-sm.webp" onerror="this.onerror=null;this.src='/Frontend/images/favicon.png';" alt="Busia Chicken Limited - The Chicken People" class="nav-logo" width="52" height="52" fetchpriority="high" decoding="async">
                </picture>
                <div class="nav-brand-text">
                    <span class="nav-brand-name">BUSIA CHICKEN<span style="color:var(--brand-red);font-weight:900;">.</span></span>
                    <span class="nav-brand-slogan">The Chicken People</span>
                </div>
            </a>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="mobile-menu-btn" aria-label="Toggle menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!-- Navigation Content -->
            <div class="navbar-content" id="main-nav">
                <ul class="navbar-nav main-links">
                    <li><a class="nav-link<?php echo navActive('about', $currentPage); ?>" href="/Frontend/pages/about.php"><span>About</span></a></li>
                    <li><a class="nav-link<?php echo navActive('services', $currentPage); ?>" href="/Frontend/pages/services.php"><span>Services</span></a></li>
                    <li><a class="nav-link<?php echo navActive('products', $currentPage); ?>" href="/Frontend/pages/products.php"><span>Products</span></a></li>
                    <li><a class="nav-link<?php echo navActive('shop', $currentPage); ?>" href="/Frontend/pages/shop.php"><span>Shop</span> <span class="nav-badge-red">HOT</span></a></li>
                    <li><a class="nav-link<?php echo navActive('contact', $currentPage); ?>" href="/Frontend/pages/contact.php"><span>Contact</span></a></li>
                </ul>

                <ul class="navbar-nav auth-actions">
                    <?php if ($is_customer_logged_in): ?>
                        <li><a class="nav-link<?php echo navActive('dashboard', $currentPage); ?>" href="/Frontend/pages/dashboard.php"><span>Dashboard</span></a></li>
                        <li>
                            <a class="btn btn-primary nav-btn" href="/Frontend/pages/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a class="btn btn-outline nav-btn nav-signin-btn" href="/Frontend/pages/login.php">Sign In</a>
                        </li>
                        <li>
                            <a class="btn btn-primary nav-btn" href="/Frontend/pages/register.php">Get Started</a>
                        </li>
                    <?php endif; ?>

                    <li class="cart-item">
                        <a href="/Frontend/pages/cart.php" class="nav-link cart-link" aria-label="Shopping cart">
                            <i data-lucide="shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    </header>
