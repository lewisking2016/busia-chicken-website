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

    <!-- Google Fonts: Outfit (display) + Fraunces (editorial serif) + Inter (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&display=swap" rel="stylesheet">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/vendor/swiper/swiper-bundle.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/animations.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/responsive.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/premium.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/Frontend/images/busia logo.png">
</head>
<body class="<?php echo $is_home ? 'has-hero' : ''; ?>">

    <!-- Scroll progress bar -->
    <div class="scroll-progress" id="scroll-progress"></div>

    <!-- Main Navigation -->
    <nav class="navbar premium-nav" id="site-nav">
        <div class="container nav-inner">
            <!-- Brand Logo (the logo carries the name - keep it minimal) -->
            <a href="/" class="nav-brand" aria-label="Busia Chicken Farm - Home">
                <img src="/Frontend/images/busia logo.png" alt="Busia Chicken Farm" class="nav-logo">
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
                    <li><a class="nav-link<?php echo navActive('shop', $currentPage); ?>" href="/Frontend/pages/shop.php"><span>Shop</span></a></li>
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
