<?php
/**
 * Homepage — Busia Chicken Farm
 * Premium redesign: motion, storytelling, editorial polish.
 */
declare(strict_types=1);

$page_title = 'Busia Chicken Farm - Premium Poultry & Farm Management Solutions';
include 'includes/header.php';

$pdo = getDB();
?>

<!-- ═══════════════ HERO SLIDER ═══════════════ -->
<section class="hero-swiper" aria-label="Featured highlights">
    <div class="swiper-wrapper">

        <!-- Slide 1 -->
        <div class="swiper-slide hero-slide">
            <img src="/Frontend/images/download (8).png" alt="Busia Chicken Farm — poultry farm overview" fetchpriority="high">
            <div class="hero-scrim"></div>
            <div class="container">
                <div class="hero-content">
                    <span class="hero-badge hero-anim"><span class="dot"></span> Welcome to Busia Chicken Farm</span>
                    <h1 class="hero-title hero-anim">Quality Poultry for <em>East Africa</em></h1>
                    <p class="hero-sub hero-anim">Premium broilers, layers and day-old chicks — raised with care, delivered with confidence, and backed by a decade of sustainable farming excellence.</p>
                    <div class="hero-cta hero-anim">
                        <a href="/Frontend/pages/shop.php" class="btn btn-primary" data-magnetic>Explore Products</a>
                        <a href="/Frontend/pages/about.php" class="btn btn-outline">Our Story</a>
                    </div>
                    <div class="hero-stats hero-anim">
                        <div class="hero-stat"><b><span data-counter="10" data-suffix="k+">0</span></b><span>Chickens raised annually</span></div>
                        <div class="hero-stat"><b><span data-counter="5" data-suffix="k+">0</span></b><span>Happy customers</span></div>
                        <div class="hero-stat"><b><span data-counter="10" data-suffix="+">0</span></b><span>Years in business</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="swiper-slide hero-slide">
            <img src="/Frontend/images/download (4).png" alt="Premium egg production">
            <div class="hero-scrim"></div>
            <div class="container">
                <div class="hero-content">
                    <span class="hero-badge hero-anim"><span class="dot"></span> The Egg People</span>
                    <h1 class="hero-title hero-anim">Farm-Fresh Eggs, <em>Every Day</em></h1>
                    <p class="hero-sub hero-anim">State-of-the-art layer facilities producing the freshest, most nutritious eggs — harvested daily and delivered to your family or business.</p>
                    <div class="hero-cta hero-anim">
                        <a href="/Frontend/pages/shop.php?category=eggs" class="btn btn-primary" data-magnetic>Order Fresh Eggs</a>
                        <a href="/Frontend/pages/products.php" class="btn btn-outline">See Our Products</a>
                    </div>
                    <div class="hero-stats hero-anim">
                        <div class="hero-stat"><b><span data-counter="300" data-suffix="+">0</span></b><span>Eggs per bird per year</span></div>
                        <div class="hero-stat"><b><span data-counter="100" data-suffix="%">0</span></b><span>Biosafety compliant</span></div>
                        <div class="hero-stat"><b><span data-counter="24" data-suffix="h">0</span></b><span>Response time</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="swiper-slide hero-slide">
            <img src="/Frontend/images/download (2).png" alt="Expert farm management team">
            <div class="hero-scrim"></div>
            <div class="container">
                <div class="hero-content">
                    <span class="hero-badge hero-anim"><span class="dot"></span> Expert Farm Management</span>
                    <h1 class="hero-title hero-anim">Trusted by <em>Thousands</em></h1>
                    <p class="hero-sub hero-anim">From consulting and incubator rentals to premium feeds — our dedicated team helps farmers grow with confidence.</p>
                    <div class="hero-cta hero-anim">
                        <a href="/Frontend/pages/services.php" class="btn btn-primary" data-magnetic>Explore Services</a>
                        <a href="/Frontend/pages/contact.php" class="btn btn-outline">Partner With Us</a>
                    </div>
                    <div class="hero-stats hero-anim">
                        <div class="hero-stat"><b><span data-counter="95" data-suffix="%+">0</span></b><span>Hatch success rate</span></div>
                        <div class="hero-stat"><b><span data-counter="40" data-suffix="%">0</span></b><span>Saved on feed costs</span></div>
                        <div class="hero-stat"><b><span data-counter="6" data-suffix="-7">0</span></b><span>Weeks to market weight</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="swiper-pagination hero-pagination"></div>
    <div class="scroll-cue"><div class="mouse"></div>Scroll</div>
</section>

<!-- ═══════════════ MARQUEE TRUST BAND ═══════════════ -->
<div class="marquee" aria-hidden="true">
    <div class="marquee-track">
        <?php
        $words = ['AUTHENTICITY', 'QUALITY', 'RELIABILITY', 'SUSTAINABILITY', 'INNOVATION', 'EXCELLENCE', 'TRUSTED', 'NUTRITIOUS'];
        for ($copy = 0; $copy < 2; $copy++) {
            foreach ($words as $w) {
                echo '<span>' . $w . '</span>';
            }
        }
        ?>
    </div>
</div>

<!-- ═══════════════ WHY CHOOSE US ═══════════════ -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Why Busia Chicken</span>
            <h2 class="section-title">Farming done <em>right</em>, from hatch to harvest</h2>
            <p class="lead">We combine traditional farming wisdom with modern technology to deliver the best poultry products in the region.</p>
        </div>

        <div class="grid-3" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.8rem;">
            <div class="p-card">
                <div class="p-icon"><i data-lucide="shield-check" style="width:28px;height:28px;"></i></div>
                <h3>Premium Quality</h3>
                <p>Rigorous health checks, premium feeds and strict biosafety protocols ensure our birds are the healthiest in the region.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="truck" style="width:28px;height:28px;"></i></div>
                <h3>Reliable Delivery</h3>
                <p>Timely, safe transportation of live birds and fresh eggs — direct to your farm or business, in carefully monitored transit.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="graduation-cap" style="width:28px;height:28px;"></i></div>
                <h3>Expert Support</h3>
                <p>Consulting, incubator rentals and feed-formulation training help you maximize yield and cut operating costs.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="egg" style="width:28px;height:28px;"></i></div>
                <h3>Farm-Fresh Eggs</h3>
                <p>Grade-A eggs harvested daily from modern layer houses — packed in 30-egg trays for homes, shops and hotels.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="dna" style="width:28px;height:28px;"></i></div>
                <h3>Superior Genetics</h3>
                <p>Ross 308, Cobb 500, ISA Brown and Lohmann breeds — vaccinated, disease-free and bred for performance.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="heart-handshake" style="width:28px;height:28px;"></i></div>
                <h3>Customer First</h3>
                <p>Transparent pricing, honest advice and long-term partnerships with the farmers and families we serve.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ STATS BAND ═══════════════ -->
<section class="section-pad stats-band">
    <div class="container">
        <div class="grid-4" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.6rem;">
            <div class="stat-item" data-reveal>
                <b><span data-counter="10" data-suffix="k+">0</span></b>
                <span>Chickens Raised Annually</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="120">
                <b><span data-counter="5" data-suffix="k+">0</span></b>
                <span>Satisfied Customers</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="240">
                <b><span data-counter="10" data-suffix="+">0</span></b>
                <span>Years in Business</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="360">
                <b><span data-counter="100" data-suffix="%">0</span></b>
                <span>Quality Guarantee</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ FEATURED PRODUCTS ═══════════════ -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:1.6rem;flex-wrap:wrap;margin-bottom:2.4rem;" data-reveal>
            <div>
                <span class="eyebrow">Best Sellers</span>
                <h2 class="section-title" style="margin-bottom:0.4rem;">Featured <em>Products</em></h2>
                <p class="lead" style="margin:0;">Premium poultry, eggs and feeds — ready to order.</p>
            </div>
            <a href="/Frontend/pages/shop.php" class="btn btn-outline">View Full Shop <i data-lucide="arrow-right" style="width:16px;height:16px;"></i></a>
        </div>

        <div style="position:relative;padding:0 6px;">
            <div class="swiper creative-slider swiper-products">
                <div class="swiper-wrapper">
                <?php
                require_once __DIR__ . '/includes/product_source.php';
                $products = loadDisplayProducts($pdo);
                if (!empty($products)) {
                    $products = array_slice($products, 0, 8);
                }

                foreach ($products as $index => $product):
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
                        <a href="/Frontend/pages/product-detail.php?id=<?php echo $product['id']; ?>" style="display:block;text-decoration:none;color:inherit;">
                            <div class="product-image">
                                <span class="product-badge">Top Rated</span>
                                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                            </div>
                        </a>
                        <div class="product-body">
                            <h4 class="product-name"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="product-description"><?php echo htmlspecialchars($product['description'] ?? $product['desc'] ?? 'Premium quality poultry product.'); ?></p>
                            <div class="product-meta">
                                <span class="product-price">KES <?php echo number_format((float)$product['price'], 0); ?></span>
                            </div>
                            <button class="add-to-cart-btn btn btn-primary" data-id="<?php echo $product['id']; ?>" data-qty="1" style="width:100%;justify-content:center;">
                                <span>Add to Cart</span>
                                <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>

            <div class="swiper-button-prev creative-nav-prev"></div>
            <div class="swiper-button-next creative-nav-next"></div>
        </div>
    </div>
</section>

<!-- ═══════════════ PROCESS ═══════════════ -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">How It Works</span>
            <h2 class="section-title">From our farm to <em>your table</em></h2>
            <p class="lead">A simple, transparent journey — so you always know exactly what you're getting.</p>
        </div>

        <div class="grid-4" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1.8rem;">
            <div class="p-card num-card">
                <span class="step-no">01</span>
                <h3>Select &amp; Order</h3>
                <p>Choose your chicks, birds, eggs or feeds online or by phone. Our team confirms availability instantly.</p>
            </div>
            <div class="p-card num-card">
                <span class="step-no">02</span>
                <h3>Vaccinated &amp; Checked</h3>
                <p>Every bird passes health and vaccination protocols before it leaves the farm gates.</p>
            </div>
            <div class="p-card num-card">
                <span class="step-no">03</span>
                <h3>Packed &amp; Delivered</h3>
                <p>Carefully packed and transported in monitored conditions — on time, every time.</p>
            </div>
            <div class="p-card num-card">
                <span class="step-no">04</span>
                <h3>Grow With Support</h3>
                <p>Ongoing consulting, feed advice and after-sale support to help your flock thrive.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ STORY TEASER ═══════════════ -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4.5rem);align-items:center;">
        <div class="img-frame frame-gold" data-reveal="left">
            <img src="/Frontend/images/download (2).png" alt="Busia Chicken Farm team" loading="lazy" style="aspect-ratio:4/3;" data-parallax="0.06">
        </div>
        <div data-reveal="right">
            <span class="eyebrow">Our Story</span>
            <h2 class="section-title">From 500 birds to <em>East Africa's</em> trusted farm</h2>
            <p class="lead">Founded in 2015 in Nasira AC, Busia, we've grown from a small family operation into a modern poultry facility serving thousands of customers — without ever losing the personal touch.</p>
            <ul class="check-list" style="margin:1.6rem 0 2.2rem;">
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>State-of-the-art incubation &amp; biosafety facilities</li>
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Vaccinated, disease-free birds with <em>95%+ hatch rates</em></li>
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Feed formulas that cut costs by up to <em>40%</em></li>
            </ul>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <a href="/Frontend/pages/about.php" class="btn btn-primary" data-magnetic>Read Our Story</a>
                <a href="/Frontend/pages/services.php" class="btn btn-outline">Explore Services</a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ TESTIMONIALS ═══════════════ -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Testimonials</span>
            <h2 class="section-title">Farmers who <em>trust us</em></h2>
        </div>

        <div class="grid-3" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.8rem;">
            <figure class="testimonial-card">
                <span class="quote-mark">&ldquo;</span>
                <div class="stars">★★★★★</div>
                <blockquote>I've bought day-old chicks from Busia for three seasons now. Mortality is almost zero and the support team actually answers the phone.</blockquote>
                <figcaption>
                    <span class="avatar">JM</span>
                    <span><span class="t-name">James Muriithi</span><br><span class="t-role">Broiler Farmer, Eldoret</span></span>
                </figcaption>
            </figure>
            <figure class="testimonial-card">
                <span class="quote-mark">&ldquo;</span>
                <div class="stars">★★★★★</div>
                <blockquote>The incubator rental service changed our hatchery. We now hatch over 2,000 chicks a month with an amazing success rate.</blockquote>
                <figcaption>
                    <span class="avatar">AN</span>
                    <span><span class="t-name">Alice Nekesa</span><br><span class="t-role">Hatchery Owner, Bungoma</span></span>
                </figcaption>
            </figure>
            <figure class="testimonial-card">
                <span class="quote-mark">&ldquo;</span>
                <div class="stars">★★★★★</div>
                <blockquote>Fresh eggs delivered to our restaurant every morning, without fail. The quality is unmatched in the county.</blockquote>
                <figcaption>
                    <span class="avatar">DO</span>
                    <span><span class="t-name">Daniel Ochieng</span><br><span class="t-role">Restaurant Owner, Busia Town</span></span>
                </figcaption>
            </figure>
        </div>
    </div>
</section>

<!-- ═══════════════ CTA ═══════════════ -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="cta-band" data-reveal="zoom">
            <div class="container" style="padding:0;">
                <span class="eyebrow centered" style="color:var(--gold-300);">Let's Get Started</span>
                <h2>Ready to elevate your farm?</h2>
                <p>Join thousands of successful farmers using Busia Chicken products and management tools. Whether you're a family, a restaurant or a commercial operation — we've got you covered.</p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <a href="/Frontend/pages/register.php" class="btn btn-primary" data-magnetic>Create Account</a>
                    <a href="/Frontend/pages/contact.php" class="btn btn-ghost">Contact Sales</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
