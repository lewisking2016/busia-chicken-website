<?php
/**
 * Global Header & Navigation
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/vendor/swiper/swiper-bundle.min.css">

    <!-- Premium Stylesheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/animations.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/responsive.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/Frontend/images/busia logo.png">
</head>
<body>

    <!-- Main Navigation -->
    <nav class="navbar">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; position: relative;">
            <!-- Brand Logo -->
            <a href="/" style="display: flex; align-items: center; gap: 12px; text-decoration: none; z-index: 10;">
                <img src="/Frontend/images/busia logo.png" alt="Busia Chicken Farm Logo" style="height: 48px; width: auto; object-fit: contain;">
            </a>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" style="display: none; background: none; border: none; cursor: pointer; padding: 8px; flex-direction: column; gap: 5px; z-index: 10;" aria-label="Toggle menu">
                <span style="display: block; width: 24px; height: 2px; background: var(--dark); transition: transform 0.3s;"></span>
                <span style="display: block; width: 24px; height: 2px; background: var(--dark); transition: opacity 0.3s;"></span>
                <span style="display: block; width: 24px; height: 2px; background: var(--dark); transition: transform 0.3s;"></span>
            </button>

            <!-- Desktop Navigation -->
            <div class="navbar-content" id="main-nav">
                <ul class="navbar-nav main-links">
                    <li><a class="nav-link<?php echo navActive('home', $currentPage); ?>" href="/">Home</a></li>
                    <li><a class="nav-link<?php echo navActive('about', $currentPage); ?>" href="/Frontend/pages/about.php">About</a></li>
                    <li><a class="nav-link<?php echo navActive('products', $currentPage); ?>" href="/Frontend/pages/products.php">Products</a></li>
                    <li><a class="nav-link<?php echo navActive('shop', $currentPage); ?>" href="/Frontend/pages/shop.php">Shop</a></li>
                    <li><a class="nav-link<?php echo navActive('contact', $currentPage); ?>" href="/Frontend/pages/contact.php">Contact</a></li>
                </ul>

                <ul class="navbar-nav auth-actions">
                    <?php if ($is_customer_logged_in): ?>
                        <li><a class="nav-link<?php echo navActive('dashboard', $currentPage); ?>" href="/Frontend/pages/dashboard.php">Dashboard</a></li>
                        <li>
                            <a class="btn btn-outline nav-btn" href="/Frontend/pages/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a class="btn btn-outline nav-btn" href="/Frontend/pages/login.php">Login</a>
                        </li>
                        <li>
                            <a class="btn btn-primary nav-btn" href="/Frontend/pages/register.php">Register</a>
                        </li>
                    <?php endif; ?>

                    <li class="cart-item">
                        <a href="/Frontend/pages/cart.php" class="nav-link cart-link">
                            <i data-lucide="shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <style>
        @media (max-width: 768px) {
            #mobile-menu-btn { display: flex !important; }
        }
    </style>