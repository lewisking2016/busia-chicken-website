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
                        <img src="/Frontend/images/busia logo.png" alt="Busia Chicken Farm Logo">
                        <span class="f-brand-text">BUSIA<span>CHICKEN</span></span>
                    </div>
                    <p class="f-desc">
                        Leading supplier of premium-grade poultry, fresh eggs, and specially formulated feeds across East Africa. From our family farm to your table — quality you can trust.
                    </p>
                    <div class="f-socials">
                        <a href="#" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
                        <a href="#" aria-label="Twitter / X"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg></a>
                        <a href="#" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>
                        <a href="#" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg></a>
                    </div>
                </div>

                <!-- Company -->
                <div>
                    <h4>Company</h4>
                    <ul>
                        <li><a href="/Frontend/pages/about.php">About Us</a></li>
                        <li><a href="/Frontend/pages/services.php">Our Services</a></li>
                        <li><a href="/Frontend/pages/faq.php">FAQ</a></li>
                        <li><a href="/Frontend/pages/contact.php">Contact</a></li>
                    </ul>
                </div>

                <!-- Shop -->
                <div>
                    <h4>Shop</h4>
                    <ul>
                        <li><a href="/Frontend/pages/products.php">All Products</a></li>
                        <li><a href="/Frontend/pages/shop.php?category=chicks">Day-Old Chicks</a></li>
                        <li><a href="/Frontend/pages/shop.php?category=feeds">Feeds</a></li>
                        <li><a href="/Frontend/pages/shop.php?category=eggs">Fresh Eggs</a></li>
                        <li><a href="/Frontend/pages/cart.php">Your Cart</a></li>
                    </ul>
                </div>

                <!-- Contact + Newsletter -->
                <div>
                    <h4>Get In Touch</h4>
                    <ul class="f-contact">
                        <li><i data-lucide="phone" style="width:16px;height:16px;"></i><a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $site_phone); ?>"><?php echo htmlspecialchars($site_phone, ENT_QUOTES, 'UTF-8'); ?></a></li>
                        <li><i data-lucide="mail" style="width:16px;height:16px;"></i><a href="mailto:<?php echo htmlspecialchars($site_email, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($site_email, ENT_QUOTES, 'UTF-8'); ?></a></li>
                        <li><i data-lucide="map-pin" style="width:16px;height:16px;"></i><span><?php echo htmlspecialchars($site_address, ENT_QUOTES, 'UTF-8'); ?></span></li>
                    </ul>
                    <div class="f-newsletter">
                        <p>Join our newsletter for farm updates &amp; special offers.</p>
                        <form>
                            <input type="email" placeholder="Your email address" aria-label="Email address" required>
                            <button type="submit">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="p-footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.</p>
                <div style="display: flex; gap: 1.4rem;">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Vendor Scripts -->
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/gsap/gsap.min.js"></script>
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/lucide/lucide.min.js"></script>

    <!-- App Scripts -->
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/js/main.js" defer></script>
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/js/premium.js" defer></script>
</body>
</html>
