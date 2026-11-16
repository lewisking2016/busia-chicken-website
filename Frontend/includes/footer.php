<?php
/**
 * Global Footer — Premium Redesign
 */
declare(strict_types=1);

if (!isset($path_prefix)) {
    $path_prefix = '';
}

$site_name = function_exists('getSetting') ? getSetting('farm_name', 'Busia Chicken Farm') : 'Busia Chicken Farm';
$site_email = function_exists('getSetting') ? getSetting('farm_email', 'info@busiachicken.com') : 'info@busiachicken.com';
$site_phone = function_exists('getSetting') ? getSetting('farm_phone', '+254 727 585 599') : '+254 727 585 599';
$site_address = function_exists('getSetting') ? getSetting('farm_address', 'Nasira AC Sub-location, Busia, Kenya') : 'Nasira AC Sub-location, Busia, Kenya';
?>

    <!-- Footer -->
    <footer class="p-footer">
        <div class="container">
            <div class="p-footer-grid">
                <!-- Brand -->
                <div>
                    <div class="f-brand">
                        <img src="/Frontend/images/logo.svg" onerror="this.onerror=null;this.src='/Frontend/images/busia_chicken_logo.png';" alt="Busia Chicken Limited Logo">
                        <div class="f-brand-text-block">
                            <span class="f-brand-text">BUSIA <span>CHICKEN</span></span>
                            <span class="f-brand-slogan">The Chicken People</span>
                        </div>
                    </div>
                    <p class="f-desc">
                        Leading supplier of premium-grade poultry, fresh eggs, organic fertilizers and specially formulated animal feeds across East Africa. From our family farm to your table — quality you can trust since 2015.
                    </p>
                    <div class="f-socials">
                        <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
                        <a href="https://x.com" target="_blank" rel="noopener" aria-label="Twitter / X"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg></a>
                        <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>
                        <a href="https://wa.me/254727585599?text=Hello%20Busia%20Chicken%20Farm,%20I%20would%20like%20to%20order" target="_blank" rel="noopener" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg></a>
                    </div>
                </div>

                <!-- Company -->
                <div>
                    <h4>Company</h4>
                    <ul>
                        <li><a href="/Frontend/pages/about.php">About Us</a></li>
                        <li><a href="/Frontend/pages/services.php">Our Services</a></li>
                        <li><a href="/Frontend/pages/faq.php">FAQ</a></li>
                        <li><a href="/Frontend/pages/contact.php">Contact &amp; Locations</a></li>
                    </ul>
                </div>

                <!-- Shop -->
                <div>
                    <h4>Shop Products</h4>
                    <ul>
                        <li><a href="/Frontend/pages/products.php">All Products</a></li>
                        <li><a href="/Frontend/pages/shop.php?category=chicks">Day-Old Chicks</a></li>
                        <li><a href="/Frontend/pages/shop.php?category=feeds">Animal Feeds</a></li>
                        <li><a href="/Frontend/pages/shop.php?category=eggs">Farm-Fresh Eggs</a></li>
                        <li><a href="/Frontend/pages/cart.php">Shopping Cart</a></li>
                    </ul>
                </div>

                <!-- Contact & Two Locations -->
                <div>
                    <h4>Our Two Locations</h4>
                    <ul class="f-contact">
                        <li>
                            <i data-lucide="map-pin" style="width:16px;height:16px;color:var(--gold-400);"></i>
                            <div>
                                <strong>Farm HQ &amp; Hatchery:</strong>
                                <span>Nasira AC Sub-location, Busia, Kenya</span>
                            </div>
                        </li>
                        <li>
                            <i data-lucide="store" style="width:16px;height:16px;color:var(--gold-400);"></i>
                            <div>
                                <strong>Tangakona Branch:</strong>
                                <span>Busia-Kisumu Highway, Tangakona Depot</span>
                            </div>
                        </li>
                        <li>
                            <i data-lucide="phone" style="width:16px;height:16px;"></i>
                            <a href="tel:+254727585599">+254 727 585 599</a>
                        </li>
                        <li>
                            <i data-lucide="mail" style="width:16px;height:16px;"></i>
                            <a href="mailto:info@busiachicken.com">info@busiachicken.com</a>
                        </li>
                    </ul>
                    <div class="f-newsletter">
                        <p>Join our newsletter for farm updates &amp; special discounts.</p>
                        <form onsubmit="event.preventDefault(); alert('Thank you for subscribing to Busia Chicken updates!');">
                            <input type="email" placeholder="Your email address" aria-label="Email address" required>
                            <button type="submit">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="p-footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Busia Chicken Limited. The Chicken People. All rights reserved.</p>
                <div style="display: flex; gap: 1.4rem;">
                    <a href="/Frontend/pages/faq.php">Help &amp; FAQ</a>
                    <a href="/Frontend/pages/contact.php">Directions</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Native Mobile App Bottom Navigation Bar -->
    <?php
    $curPage = basename($_SERVER['REQUEST_URI'] ?? '', '.php');
    $curPage = rtrim($curPage, '/');
    if ($curPage === '' || $curPage === 'index') $curPage = 'home';
    ?>
    <nav class="mobile-app-bar" aria-label="Mobile Navigation">
        <a href="/" class="app-bar-item <?php echo $curPage === 'home' ? 'active' : ''; ?>">
            <i data-lucide="home"></i>
            <span>Home</span>
        </a>
        <a href="/Frontend/pages/shop.php" class="app-bar-item <?php echo $curPage === 'shop' ? 'active' : ''; ?>">
            <i data-lucide="store"></i>
            <span>Shop</span>
        </a>
        <a href="/Frontend/pages/services.php" class="app-bar-item <?php echo $curPage === 'services' ? 'active' : ''; ?>">
            <i data-lucide="sparkles"></i>
            <span>Services</span>
        </a>
        <a href="/Frontend/pages/cart.php" class="app-bar-item <?php echo $curPage === 'cart' ? 'active' : ''; ?>">
            <div class="app-bar-cart-icon">
                <i data-lucide="shopping-cart"></i>
                <span class="app-bar-badge cart-count">0</span>
            </div>
            <span>Cart</span>
        </a>
        <a href="/Frontend/pages/contact.php" class="app-bar-item <?php echo $curPage === 'contact' ? 'active' : ''; ?>">
            <i data-lucide="map-pin"></i>
            <span>Locations</span>
        </a>
    </nav>

    <!-- Floating WhatsApp Action for Quick Kenyan Mobile Orders -->
    <a href="https://wa.me/254727585599?text=Hello%20Busia%20Chicken%20Farm,%20I%20am%20interested%20in%20ordering" class="floating-whatsapp-btn" target="_blank" rel="noopener" aria-label="Order on WhatsApp">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2zm.01 1.67c2.2 0 4.26.86 5.82 2.41a8.17 8.17 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.66c-.25-.12-1.47-.72-1.7-.81-.23-.08-.39-.12-.56.12-.17.25-.65.81-.8 1-.15.19-.3.21-.55.08-.25-.12-1.05-.39-2-1.24-.74-.66-1.24-1.47-1.39-1.72-.15-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.15.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.12-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.12.17 1.78 2.72 4.31 3.81.6.26 1.07.42 1.44.54.61.19 1.16.17 1.6.1.49-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.17-.48-.29z"/>
        </svg>
        <span class="whatsapp-label">Order via WhatsApp</span>
    </a>

    <!-- Vendor Scripts -->
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/gsap/gsap.min.js<?php echo assetVer('assets/vendor/gsap/gsap.min.js'); ?>"></script>
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/swiper/swiper-bundle.min.js<?php echo assetVer('assets/vendor/swiper/swiper-bundle.min.js'); ?>"></script>
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/lucide/lucide.min.js<?php echo assetVer('assets/vendor/lucide/lucide.min.js'); ?>"></script>

    <!-- App Scripts -->
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/js/main.js<?php echo assetVer('assets/js/main.js'); ?>" defer></script>
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/js/premium.js<?php echo assetVer('assets/js/premium.js'); ?>" defer></script>
</body>
</html>
