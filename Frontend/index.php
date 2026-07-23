<?php
/**
 * Homepage - Busia Chicken Farm
 * Premium Minimalist Redesign
 */
declare(strict_types=1);

$page_title = 'Busia Chicken Farm - Premium Poultry & Farm Management Solutions';
include 'includes/header.php';

$pdo = getDB();
?>

<!-- Swiper Hero Slider Section -->
<section style="padding: 0; position: relative; overflow: hidden; height: 90vh;">
    <div class="swiper hero-swiper" style="height: 100%;">
        <div class="swiper-wrapper">
            <!-- Slide 1: Farm Overview -->
            <div class="swiper-slide" style="position: relative;">
                <img src="/Frontend/images/download (8).png" alt="Farm Overview" style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to right, rgba(0,0,0,0.7), transparent); display: flex; align-items: center; padding: 0 var(--space-xl);">
                    <div class="container hero-content" style="color: white; max-width: 800px;">
                        <div style="display: inline-block; padding: 0.5rem 1rem; background: var(--primary); color: white; font-weight: 600; font-size: 0.875rem; border-radius: var(--radius-pill); margin-bottom: var(--space-lg);">
                            Welcome to Busia Chicken Farm
                        </div>
                        <h1 style="color: white;">Quality Poultry for East Africa</h1>
                        <p style="font-size: 1.25rem; margin-bottom: var(--space-2xl); color: rgba(255,255,255,0.9);">
                            Providing high-grade broilers, layers, and day-old chicks with sustainable farming excellence.
                        </p>
                        <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
                            <a href="/Frontend/pages/shop.php" class="btn btn-primary">Explore Products</a>
                            <a href="/Frontend/pages/about.php" class="btn btn-outline" style="color: white; border-color: white;">Our Story</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Slide 2: Egg Production -->
            <div class="swiper-slide" style="position: relative;">
                <img src="/Frontend/images/download (4).png" alt="Egg Production" style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to right, rgba(0,0,0,0.8), transparent); display: flex; align-items: center; padding: 0 var(--space-xl);">
                    <div class="container hero-content" style="color: white; max-width: 800px;">
                        <div style="display: inline-block; padding: 0.5rem 1rem; background: var(--accent); color: var(--dark); font-weight: 600; font-size: 0.875rem; border-radius: var(--radius-pill); margin-bottom: var(--space-lg);">
                            Premium Egg Production
                        </div>
                        <h1 style="color: white;">The Egg People</h1>
                        <p style="font-size: 1.25rem; margin-bottom: var(--space-2xl); color: rgba(255,255,255,0.9);">
                            State-of-the-art facilities ensuring the freshest, most nutritious eggs for your family.
                        </p>
                        <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
                            <a href="/Frontend/pages/shop.php?category=eggs" class="btn btn-primary">Order Fresh Eggs</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Slide 3: Our Team -->
            <div class="swiper-slide" style="position: relative;">
                <img src="/Frontend/images/download (2).png" alt="Our Team" style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to right, rgba(0,0,0,0.8), transparent); display: flex; align-items: center; padding: 0 var(--space-xl);">
                    <div class="container hero-content" style="color: white; max-width: 800px;">
                        <div style="display: inline-block; padding: 0.5rem 1rem; background: var(--primary); color: white; font-weight: 600; font-size: 0.875rem; border-radius: var(--radius-pill); margin-bottom: var(--space-lg);">
                            Expert Farm Management
                        </div>
                        <h1 style="color: white;">Trusted by Thousands</h1>
                        <p style="font-size: 1.25rem; margin-bottom: var(--space-2xl); color: rgba(255,255,255,0.9);">
                            Our dedicated team ensures the highest standards of poultry health and quality.
                        </p>
                        <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
                            <a href="/Frontend/pages/contact.php" class="btn btn-primary">Partner With Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Pagination -->
        <div class="swiper-pagination hero-pagination"></div>
    </div>
</section>

<!-- Minimalist Words Slider / Trust -->
<section class="bg-gray" style="padding: var(--space-xl) 0; overflow: hidden;">
    <div class="container">
        <div class="swiper trust-swiper">
            <div class="swiper-wrapper" style="align-items: center;">
                <div class="swiper-slide" style="text-align: center;"><h3 style="color: var(--gray-400); font-family: 'Outfit';">AUTHENTICITY</h3></div>
                <div class="swiper-slide" style="text-align: center;"><h3 style="color: var(--gray-400); font-family: 'Outfit';">QUALITY</h3></div>
                <div class="swiper-slide" style="text-align: center;"><h3 style="color: var(--gray-400); font-family: 'Outfit';">RELIABILITY</h3></div>
                <div class="swiper-slide" style="text-align: center;"><h3 style="color: var(--gray-400); font-family: 'Outfit';">SUSTAINABILITY</h3></div>
                <div class="swiper-slide" style="text-align: center;"><h3 style="color: var(--gray-400); font-family: 'Outfit';">INNOVATION</h3></div>
                <div class="swiper-slide" style="text-align: center;"><h3 style="color: var(--gray-400); font-family: 'Outfit';">EXCELLENCE</h3></div>
                <div class="swiper-slide" style="text-align: center;"><h3 style="color: var(--gray-400); font-family: 'Outfit';">TRUSTED</h3></div>
                <div class="swiper-slide" style="text-align: center;"><h3 style="color: var(--gray-400); font-family: 'Outfit';">NUTRITIOUS</h3></div>
            </div>
        </div>
    </div>
</section>

<!-- Clean Feature Section -->
<section>
    <div class="container">
        <div class="section-header">
            <h2>Why Choose Busia Chicken?</h2>
            <p>We combine traditional farming wisdom with modern technology to deliver the best poultry products.</p>
        </div>
        
        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-2xl);">
            <div class="feature-card live-float">
                <div style="width: 56px; height: 56px; background: var(--gray-50); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-lg); color: var(--primary);">
                    <i data-lucide="shield-check" style="width: 28px; height: 28px;"></i>
                </div>
                <h4 style="margin-bottom: var(--space-sm);">Premium Quality</h4>
                <p style="font-size: 0.95rem; margin: 0; color: var(--gray-600);">Rigorous health checks and premium feeds ensure our birds are the healthiest in the region.</p>
            </div>
            <div class="feature-card live-float">
                <div style="width: 56px; height: 56px; background: var(--gray-50); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-lg); color: var(--primary);">
                    <i data-lucide="truck" style="width: 28px; height: 28px;"></i>
                </div>
                <h4 style="margin-bottom: var(--space-sm);">Reliable Delivery</h4>
                <p style="font-size: 0.95rem; margin: 0; color: var(--gray-600);">Timely and safe transportation of live birds and fresh eggs directly to your farm or business.</p>
            </div>
            <div class="feature-card live-float">
                <div style="width: 56px; height: 56px; background: var(--gray-50); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-lg); color: var(--primary);">
                    <i data-lucide="book-open" style="width: 28px; height: 28px;"></i>
                </div>
                <h4 style="margin-bottom: var(--space-sm);">Expert Support</h4>
                <p style="font-size: 0.95rem; margin: 0; color: var(--gray-600);">Our farm management software and expert consultants help you maximize your yield.</p>
            </div>
        </div>
    </div>
</section>

<!-- Elegant E-Commerce Showcase -->
<section style="background-color: var(--gray-50); position: relative; overflow: hidden;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-3xl);">
            <div style="max-width: 600px;">
                <div style="display: inline-block; padding: 0.5rem 1rem; background: var(--white); color: var(--primary); font-weight: 600; font-size: 0.875rem; border-radius: var(--radius-pill); margin-bottom: var(--space-md); border: 1px solid var(--gray-200);">
                    Best Sellers
                </div>
                <h2>Featured Products</h2>
                <p>Browse our selection of premium poultry and feeds.</p>
            </div>
            <div style="display: flex; gap: var(--space-md); align-items: center; margin-bottom: 1rem;">
                <a href="/Frontend/pages/shop.php" class="btn btn-outline">View Full Shop</a>
            </div>
        </div>

        <!-- Slider Container with Arrows & Progress -->
        <div style="position: relative; padding: 0 20px;">
            <div class="swiper swiper-products creative-slider">
                <div class="swiper-wrapper">
                <?php
                // Load products from the shared product source (DB or fallback)
                require_once __DIR__ . '/includes/product_source.php';
                $products = loadDisplayProducts($pdo);
                // Limit to 8 featured items
                if (!empty($products)) {
                    $products = array_slice($products, 0, 8);
                }

                foreach ($products as $index => $product):
                    // Image resolution: prefer explicit `img`, then `image_url`, then fall back by product_type
                    $img = $product['img'] ?? $product['image_url'] ?? '';
                    if (!$img) {
                        $type = $product['product_type'] ?? 'feed';
                        $img = match($type) {
                            'feed' => '/Frontend/images/Growers Mash.png',
                            'eggs' => '/Frontend/images/download (3).png',
                            'chicks' => '/Frontend/images/download (7).png',
                            'live_chicken' => '/Frontend/images/download (4).png',
                            default => '/Frontend/images/Chick Starter Crumbs.png'
                        };
                    }
                ?>
                <div class="swiper-slide">
                        <div class="product-card creative-card" data-id="<?php echo $product['id']; ?>" data-type="<?php echo htmlspecialchars($product['product_type'] ?? '', ENT_QUOTES); ?>" data-instock="<?php echo (!empty($product['stock_quantity']) && $product['stock_quantity'] > 0) ? '1' : '0'; ?>">
                        <div class="product-image">
                            <span class="product-badge">Top Rated</span>
                            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="product-body">
                            <h4 class="product-name" data-gsap="title"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <p class="product-description" data-gsap="desc"><?php echo htmlspecialchars($product['description'] ?? $product['desc'] ?? 'Premium quality poultry product.'); ?></p>
                            <div class="product-meta" data-gsap="meta">
                                <span class="product-price">KES <?php echo number_format((float)$product['price'], 0) ?></span>
                            </div>
                            <button class="add-to-cart-btn btn btn-primary creative-btn" data-id="<?php echo $product['id'] ?>" data-qty="1" data-gsap="btn">
                                <span>Add to Cart</span>
                                <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
                
                <!-- Custom Progress Bar -->
                <div class="slider-progress-container">
                    <div class="slider-progress-bar"></div>
                </div>
            </div>

            <!-- Enhanced Navigation -->
            <div class="swiper-button-prev creative-nav-prev"></div>
            <div class="swiper-button-next creative-nav-next"></div>
        </div>
    </div>
</section>

<!-- Minimal CTA -->
<section class="bg-dark" style="text-align: center; padding: var(--space-4xl) 0;">
    <div class="container" style="max-width: 600px;">
        <h2 style="margin-bottom: var(--space-lg);">Ready to elevate your farm?</h2>
        <p style="margin-bottom: var(--space-2xl);">Join thousands of successful farmers using Busia Chicken products and management tools.</p>
        <div style="display: flex; gap: var(--space-md); justify-content: center;">
            <a href="/Frontend/pages/register.php" class="btn btn-primary">Create Account</a>
            <a href="/Frontend/pages/contact.php" class="btn btn-outline" style="border-color: var(--gray-600); color: var(--white);">Contact Sales</a>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>