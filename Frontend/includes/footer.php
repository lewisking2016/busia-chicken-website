<?php
/**
 * Global Footer template for Frontend pages.
 * Clean, minimal redesign.
 */
declare(strict_types=1);

if (!isset($path_prefix)) {
    $path_prefix = '';
}

$site_name = function_exists('getSetting') ? getSetting('farm_name', 'Busia Chicken Farm') : 'Busia Chicken Farm';
$site_email = function_exists('getSetting') ? getSetting('farm_email', 'info@busiachicken.com') : 'info@busiachicken.com';
$site_phone = function_exists('getSetting') ? getSetting('farm_phone', '+254 727 585 599') : '+254 727 585 599';
?>

    <!-- Footer Section -->
    <footer style="background-color: var(--gray-50); color: var(--gray-600); padding: var(--space-4xl) 0 var(--space-xl); border-top: 1px solid var(--gray-200);">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-3xl); margin-bottom: var(--space-3xl);">
                <!-- Column 1: Info -->
                <div style="grid-column: span 2;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: var(--space-md);">
                        <img src="/Frontend/images/busia logo.png" alt="Busia Chicken Farm Logo" style="height: 60px; width: auto; object-fit: contain;">
                        <span style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--dark); letter-spacing: 0.5px; display: none;">
                            BUSIA<span style="color: var(--primary);">CHICKEN</span>
                        </span>
                    </div>
                        <p style="font-size: 0.95rem; margin-bottom: var(--space-md); max-width: 400px;">
                            Leading supplier of premium grade poultry products, feed management tools, and expert consulting in East Africa.
                        </p>
                        <p style="font-size: 0.95rem; color: var(--dark); font-weight: 500;">
                        <?php echo htmlspecialchars($site_phone, ENT_QUOTES, 'UTF-8'); ?><br>
                        <?php echo htmlspecialchars($site_email, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                </div>

                <!-- Column 2: Quick Links -->
                <div>
                    <h4 style="color: var(--dark); margin-bottom: var(--space-md);">Company</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: var(--space-sm); font-size: 0.95rem;">
                        <li><a href="/Frontend/pages/about.php" style="color: var(--gray-600);">About Us</a></li>
                        <li><a href="/Frontend/pages/faq.php" style="color: var(--gray-600);">FAQ</a></li>
                        <li><a href="/Frontend/pages/contact.php" style="color: var(--gray-600);">Contact</a></li>
                    </ul>
                </div>

                <!-- Column 3: Shop -->
                <div>
                    <h4 style="color: var(--dark); margin-bottom: var(--space-md);">Shop</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: var(--space-sm); font-size: 0.95rem;">
                        <li><a href="/Frontend/pages/products.php" style="color: var(--gray-600);">All Products</a></li>
                        <li><a href="/Frontend/pages/shop.php?category=chicks" style="color: var(--gray-600);">Day-Old Chicks</a></li>
                        <li><a href="/Frontend/pages/shop.php?category=feeds" style="color: var(--gray-600);">Feeds</a></li>
                    </ul>
                </div>
            </div>

            <div style="border-top: 1px solid var(--gray-200); padding-top: var(--space-xl); display: flex; justify-content: space-between; flex-wrap: wrap; font-size: 0.875rem;">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.</p>
                <div style="display: flex; gap: var(--space-md);">
                    <a href="#" style="color: var(--gray-600);">Privacy Policy</a>
                    <a href="#" style="color: var(--gray-600);">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Global Javascript Files -->
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/gsap/gsap.min.js"></script>
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/vendor/lucide/lucide.min.js"></script>
    
    <!-- Initialize Animations using GSAP instead of Motion CDN to avoid network issues -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Elegant hero animations
            if (typeof gsap !== 'undefined') {
                const heroContent = document.querySelectorAll('.hero-content > *');
                if (heroContent.length > 0) {
                    gsap.fromTo(heroContent, 
                        { opacity: 0, y: 30 },
                        { opacity: 1, y: 0, duration: 0.8, stagger: 0.1, ease: "power2.out" }
                    );
                }

                const heroImage = document.querySelector('.hero-image');
                if (heroImage) {
                    gsap.fromTo(heroImage,
                        { opacity: 0, scale: 0.95 },
                        { opacity: 1, scale: 1, duration: 1, delay: 0.2, ease: "power2.out" }
                    );
                }

                // Simple Scroll animations for sections
                const observerOptions = {
                    root: null,
                    rootMargin: '0px 0px -100px 0px',
                    threshold: 0.1
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            gsap.fromTo(entry.target,
                                { opacity: 0, y: 40 },
                                { opacity: 1, y: 0, duration: 0.8, ease: "power2.out" }
                            );
                            observer.unobserve(entry.target);
                        }
                    });
                }, observerOptions);

                document.querySelectorAll('section:not(:first-of-type)').forEach(section => {
                    section.style.opacity = '0';
                    observer.observe(section);
                });

                // Animate cards on scroll with stagger
                const cardObserver = new IntersectionObserver((entries) => {
                    const visibleCards = entries
                        .filter(entry => entry.isIntersecting)
                        .map(entry => entry.target);
                    
                    if (visibleCards.length > 0) {
                        gsap.fromTo(visibleCards,
                            { opacity: 0, y: 40, scale: 0.95 },
                            { opacity: 1, y: 0, scale: 1, duration: 0.8, stagger: 0.15, ease: "power2.out" }
                        );
                        visibleCards.forEach(card => cardObserver.unobserve(card));
                    }
                }, observerOptions);

                document.querySelectorAll('.product-card, .card, .dashboard-card, .stat-box').forEach(card => {
                    card.style.opacity = '0';
                    cardObserver.observe(card);
                });
                
                // Animate typography on scroll
                const typoObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            gsap.fromTo(entry.target,
                                { opacity: 0, y: 20 },
                                { opacity: 1, y: 0, duration: 0.7, ease: "power2.out" }
                            );
                            typoObserver.unobserve(entry.target);
                        }
                    });
                }, observerOptions);

                document.querySelectorAll('h1, h2, h3, .section-header p').forEach(typo => {
                    // avoid re-animating hero content
                    if (!typo.closest('.hero-content')) {
                        typo.style.opacity = '0';
                        typoObserver.observe(typo);
                    }
                });
            }
        });
    </script>

    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/js/main.js" defer></script>
    <script src="<?php echo BASE_URL ?? '/Frontend/'; ?>assets/js/professional-animations.js" defer></script>
</body>
</html>
