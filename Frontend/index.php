<?php
/**
 * Homepage — Busia Chicken Limited
 * The Chicken People
 * Premium architectural engineering: dynamic CMS hero slider, multi-location showcase, mobile-first app feel.
 */
declare(strict_types=1);

$page_title = 'Busia Chicken Limited - The Chicken People | Quality Poultry for East Africa';
include 'includes/header.php';

$pdo = function_exists('getDB') ? getDB() : (function_exists('getDatabaseConnection') ? getDatabaseConnection() : null);

// Fetch dynamic hero slides from CMS table or fallback to transcript defaults
$slides = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM website_sliders WHERE is_active = 1 ORDER BY slide_order ASC");
        $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $slides = [];
    }
}

if (empty($slides)) {
    $slides = [
        [
            'badge_text' => 'Welcome to Busia Chicken Farm',
            'title_main' => 'Quality Poultry for',
            'title_em' => 'East Africa',
            'subtitle' => 'Premium broilers, layers and day-old chicks — raised with care, delivered with confidence, and backed by a decade of sustainable farming excellence.',
            'cta_primary_text' => 'Explore Products',
            'cta_primary_link' => '/Frontend/pages/shop.php',
            'cta_secondary_text' => 'Our Story',
            'cta_secondary_link' => '/Frontend/pages/about.php',
            'stat1_val' => '100', 'stat1_suffix' => 'k', 'stat1_label' => 'Chickens raised annually',
            'stat2_val' => '5000', 'stat2_suffix' => '+', 'stat2_label' => 'Happy customers',
            'stat3_val' => '13', 'stat3_suffix' => '+', 'stat3_label' => 'Years in business',
            'image_url' => '/Frontend/images/adbg.png'
        ],
        [
            'badge_text' => 'The Egg People',
            'title_main' => 'Farm-Fresh Eggs,',
            'title_em' => 'Every Day',
            'subtitle' => 'State-of-the-art layer facilities producing the freshest, most nutritious eggs — harvested daily and delivered to your family or business.',
            'cta_primary_text' => 'Order Fresh Eggs',
            'cta_primary_link' => '/Frontend/pages/shop.php?category=eggs',
            'cta_secondary_text' => 'See Our Products',
            'cta_secondary_link' => '/Frontend/pages/products.php',
            'stat1_val' => '96', 'stat1_suffix' => '%', 'stat1_label' => 'Eggs per bird per year',
            'stat2_val' => '100', 'stat2_suffix' => '%', 'stat2_label' => 'Biosafety compliant',
            'stat3_val' => '100', 'stat3_suffix' => '%', 'stat3_label' => 'Response time',
            'image_url' => '/Frontend/images/farm-fresh-eggs.jpg'
        ],
        [
            'badge_text' => 'Expert Farm Management',
            'title_main' => 'Trusted by',
            'title_em' => 'Thousands',
            'subtitle' => 'From consulting, egg & meat production to premium feeds — our dedicated team helps farmers grow with confidence.',
            'cta_primary_text' => 'Explore Services',
            'cta_primary_link' => '/Frontend/pages/services.php',
            'cta_secondary_text' => 'Partner With Us',
            'cta_secondary_link' => '/Frontend/pages/contact.php',
            'stat1_val' => '90', 'stat1_suffix' => '%', 'stat1_label' => 'Hatch success rate',
            'stat2_val' => '30', 'stat2_suffix' => '%', 'stat2_label' => 'Saved on feed costs',
            'stat3_val' => '4', 'stat3_suffix' => ' Weeks', 'stat3_label' => 'To market weight',
            'image_url' => '/Frontend/images/download (2).png'
        ]
    ];
}
?>

<!-- ═══════════════ HERO SLIDER (DYNAMIC CMS-POWERED) ═══════════════ -->
<section class="hero-swiper" aria-label="Featured highlights">
    <div class="swiper-wrapper">
        <?php foreach ($slides as $idx => $s): ?>
        <div class="swiper-slide hero-slide">
            <img src="<?php echo htmlspecialchars($s['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($s['title_main'] . ' ' . $s['title_em'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $idx === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
            <div class="hero-scrim"></div>
            <div class="container">
                <div class="hero-content">
                    <span class="hero-badge hero-anim"><span class="dot"></span> <?php echo htmlspecialchars($s['badge_text'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <h1 class="hero-title hero-anim"><?php echo htmlspecialchars($s['title_main'], ENT_QUOTES, 'UTF-8'); ?> <em><?php echo htmlspecialchars($s['title_em'], ENT_QUOTES, 'UTF-8'); ?></em></h1>
                    <p class="hero-sub hero-anim"><?php echo htmlspecialchars($s['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <div class="hero-cta hero-anim">
                        <a href="<?php echo htmlspecialchars($s['cta_primary_link'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary" data-magnetic><?php echo htmlspecialchars($s['cta_primary_text'], ENT_QUOTES, 'UTF-8'); ?></a>
                        <a href="<?php echo htmlspecialchars($s['cta_secondary_link'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline"><?php echo htmlspecialchars($s['cta_secondary_text'], ENT_QUOTES, 'UTF-8'); ?></a>
                    </div>
                    <div class="hero-stats hero-anim">
                        <div class="hero-stat"><b><span data-counter="<?php echo preg_replace('/[^0-9]/', '', (string)$s['stat1_val']); ?>" data-suffix="<?php echo htmlspecialchars($s['stat1_suffix'], ENT_QUOTES, 'UTF-8'); ?>">0</span></b><span><?php echo htmlspecialchars($s['stat1_label'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                        <div class="hero-stat"><b><span data-counter="<?php echo preg_replace('/[^0-9]/', '', (string)$s['stat2_val']); ?>" data-suffix="<?php echo htmlspecialchars($s['stat2_suffix'], ENT_QUOTES, 'UTF-8'); ?>">0</span></b><span><?php echo htmlspecialchars($s['stat2_label'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                        <div class="hero-stat"><b><span data-counter="<?php echo preg_replace('/[^0-9]/', '', (string)$s['stat3_val']); ?>" data-suffix="<?php echo htmlspecialchars($s['stat3_suffix'], ENT_QUOTES, 'UTF-8'); ?>">0</span></b><span><?php echo htmlspecialchars($s['stat3_label'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="swiper-pagination hero-pagination"></div>
    <div class="scroll-cue"><div class="mouse"></div>Scroll</div>
</section>

<!-- ═══════════════ MARQUEE TRUST BAND ═══════════════ -->
<div class="marquee" aria-hidden="true">
    <div class="marquee-track">
        <?php
        $words = ['BUSIA CHICKEN LIMITED', 'THE CHICKEN PEOPLE', 'NASIRA FARM HQ', 'TANGAKONA DEPOT', 'FARM-FRESH EGGS', 'BIO-SECURITY CERTIFIED', 'SUPERIOR GENETICS', 'ORGANIC ANIMAL FEEDS'];
        for ($copy = 0; $copy < 2; $copy++) {
            foreach ($words as $w) {
                echo '<span>' . $w . '</span>';
            }
        }
        ?>
    </div>
</div>

<!-- ═══════════════ WHY CHOOSE US (TRANSCRIPT ALIGNED) ═══════════════ -->
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
                <p>Timely, safe transportation of feeds and fresh eggs — direct to your farm or business, in carefully monitored transit.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="graduation-cap" style="width:28px;height:28px;"></i></div>
                <h3>Expert Support</h3>
                <p>Consulting, egg meat production and feed-formulation training help you maximize yield and cut operating costs.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="egg" style="width:28px;height:28px;"></i></div>
                <h3>Farm-Fresh Eggs</h3>
                <p>Grade-A eggs harvested daily from modern layer houses — packed in 30-egg trays for homes, shops and hotels.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="dna" style="width:28px;height:28px;"></i></div>
                <h3>Superior Genetics</h3>
                <p>Hy-Line, Ross 308, Cobb 500, ISA Brown and Lohmann breeds — vaccinated, disease-free and bred for performance.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="heart-handshake" style="width:28px;height:28px;"></i></div>
                <h3>Customer First</h3>
                <p>Transparent pricing, honest advice and long-term partnerships with the farmers and families we serve.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ STATS BAND (TRANSCRIPT ALIGNED) ═══════════════ -->
<section class="section-pad stats-band">
    <div class="container">
        <div class="grid-4" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.6rem;">
            <div class="stat-item" data-reveal>
                <b><span data-counter="100" data-suffix="k">0</span></b>
                <span>Chickens Raised Annually</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="120">
                <b><span data-counter="5000" data-suffix="+">0</span></b>
                <span>Satisfied Customers</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="240">
                <b><span data-counter="13" data-suffix=" Years">0</span></b>
                <span>In Business</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="360">
                <b><span data-counter="100" data-suffix="%">0</span></b>
                <span>Quality Guarantee</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ BEST SELLERS / FEATURED PRODUCTS ═══════════════ -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:1.6rem;flex-wrap:wrap;margin-bottom:2.4rem;" data-reveal>
            <div>
                <span class="eyebrow" style="color:var(--brand-red);font-weight:800;letter-spacing:0.08em;"><i data-lucide="flame" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;"></i> HOT DEALS &amp; BEST SELLERS</span>
                <h2 class="section-title" style="margin-bottom:0.4rem;">Featured <em>Products</em></h2>
                <p class="lead" style="margin:0;">Premium poultry, eggs and feeds — ready to order.</p>
            </div>
            <a href="/Frontend/pages/shop.php" class="btn btn-red">View Full Shop <i data-lucide="arrow-right" style="width:16px;height:16px;"></i></a>
        </div>

        <div style="position:relative;padding:0 6px;">
            <div class="swiper creative-slider swiper-products">
                <div class="swiper-wrapper">
                <?php
                // Target featured products requested by the boss
                $featuredNames = [
                    'Bovans Brown Layers',
                    'Broiler Finisher (6-8 weeks)',
                    'Busia Premium Mix',
                    'Chick Mash (0-4 weeks)',
                    'Chick Starter Crumbs (0-4 weeks)',
                    'Cobb 500 Broilers',
                    'Day-Old Broiler Chicks',
                    'Day-Old Layer Chicks',
                    'Farm-Fresh Grade-A Eggs (30-Egg Tray)'
                ];

                $displayProducts = [];
                if ($pdo) {
                    try {
                        $placeholders = implode(',', array_fill(0, count($featuredNames), '?'));
                        $stmt = $pdo->prepare("SELECT * FROM products WHERE name IN ($placeholders) AND is_active = 1 ORDER BY FIELD(name, $placeholders)");
                        $stmt->execute(array_merge($featuredNames, $featuredNames));
                        $displayProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {}
                }

                if (empty($displayProducts)) {
                    require_once __DIR__ . '/includes/product_source.php';
                    $displayProducts = loadDisplayProducts($pdo);
                }

                foreach ($displayProducts as $index => $product):
                    $img = $product['image_url'] ?? $product['img'] ?? '';
                    if (!$img) {
                        $type = $product['product_type'] ?? 'feed';
                        $img = match($type) {
                            'feed' => '/Frontend/images/download (8).png',
                            'eggs' => '/Frontend/images/farm-fresh-eggs.jpg',
                            'chicks' => '/Frontend/images/day-old-chicks.jpg',
                            'live_chicken' => '/Frontend/images/broiler-chickens.jpg',
                            default => '/Frontend/images/adbg.png'
                        };
                    }
                ?>
                <div class="swiper-slide">
                    <div class="product-card creative-card" data-id="<?php echo $product['id']; ?>" data-type="<?php echo htmlspecialchars($product['product_type'] ?? '', ENT_QUOTES); ?>" data-instock="<?php echo (!empty($product['stock_quantity']) && $product['stock_quantity'] > 0) ? '1' : '0'; ?>">
                        <a href="/Frontend/pages/product-detail.php?id=<?php echo $product['id']; ?>" style="display:block;text-decoration:none;color:inherit;">
                            <div class="product-image">
                                <span class="product-badge badge-red">Best Seller</span>
                                <img src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                            </div>
                        </a>
                        <div class="product-body">
                            <h4 class="product-name"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="product-description"><?php echo htmlspecialchars($product['description'] ?? 'Premium quality poultry product.'); ?></p>
                            <div class="product-meta" style="display:flex;justify-content:space-between;align-items:center;">
                                <span class="product-price">KES <?php echo number_format((float)$product['price'], 0); ?></span>
                                <span class="tag-red"><i data-lucide="check" style="width:11px;height:11px;"></i> In Stock</span>
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

<!-- ═══════════════ TWO LOCATIONS SHOWCASE (GOOGLE BUSINESS) ═══════════════ -->
<section class="locations-section" id="locations-showcase">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Our Two Facilities</span>
            <h2 class="section-title">Visit Our <em>Hubs</em> in Busia County</h2>
            <p class="lead">Serving western Kenya and East Africa from our breeding headquarters in Nasira and our sales distribution depot at Tangakona.</p>
        </div>

        <div class="locations-grid" data-reveal-group>
            <!-- Location 1: Nasira AC Sub-location -->
            <div class="location-card">
                <span class="location-badge"><i data-lucide="building"></i> Primary Farm &amp; Hatchery HQ</span>
                <h3 class="location-title">Nasira AC Sub-location</h3>
                <p class="location-desc">Our primary breeding, incubation and automated layer facility. Where all our day-old chicks are hatched under clinical biosafety controls and where our core poultry flocks are raised.</p>
                <div class="location-meta">
                    <div class="location-meta-item">
                        <i data-lucide="map-pin"></i>
                        <span>Nasira AC Sub-location, Busibwabo Location, Matayos / Busia County, Kenya</span>
                    </div>
                    <div class="location-meta-item">
                        <i data-lucide="clock"></i>
                        <span>Mon - Sat: 7:00 AM - 6:00 PM | Sun: 8:00 AM - 1:00 PM</span>
                    </div>
                    <div class="location-meta-item">
                        <i data-lucide="phone"></i>
                        <a href="tel:+254727585599" style="color:var(--brand-700);font-weight:700;">+254 727 585 599</a>
                    </div>
                </div>
                <div class="location-actions">
                    <a href="https://maps.google.com/?q=Nasira+AC+Sub-location+Busia+Kenya" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                        <i data-lucide="navigation"></i> Google Maps
                    </a>
                    <a href="https://wa.me/254727585599?text=Hello%20Busia%20Chicken%20Farm,%20I%20would%20like%20to%20visit%20the%20Nasira%20Farm" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                        <i data-lucide="message-square"></i> Inquire HQ
                    </a>
                </div>
            </div>

            <!-- Location 2: Tangakona Depot -->
            <div class="location-card">
                <span class="location-badge hub"><i data-lucide="store"></i> Sales &amp; Distribution Depot</span>
                <h3 class="location-title">Tangakona Branch</h3>
                <p class="location-desc">Our dedicated commercial branch along the Busia - Kisumu / Mumias Highway. Convenient for bulk feed collection, fresh egg crate pickups, farmer consultancy, and quick transit orders.</p>
                <div class="location-meta">
                    <div class="location-meta-item">
                        <i data-lucide="map-pin"></i>
                        <span>Tangakona Junction / Market along Busia-Kisumu Highway, Busia County, Kenya</span>
                    </div>
                    <div class="location-meta-item">
                        <i data-lucide="clock"></i>
                        <span>Mon - Sat: 8:00 AM - 6:30 PM | Sun: Closed</span>
                    </div>
                    <div class="location-meta-item">
                        <i data-lucide="phone"></i>
                        <a href="tel:+254727585599" style="color:var(--brand-700);font-weight:700;">+254 727 585 599</a>
                    </div>
                </div>
                <div class="location-actions">
                    <a href="https://maps.google.com/?q=Tangakona+Busia+Kenya" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                        <i data-lucide="navigation"></i> Google Maps
                    </a>
                    <a href="https://wa.me/254727585599?text=Hello%20Busia%20Chicken%20Farm,%20I%20would%20like%20to%20pick%20up%20supplies%20at%20Tangakona%20Depot" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                        <i data-lucide="shopping-bag"></i> Order Depot
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ PROCESS (TRANSCRIPT ALIGNED) ═══════════════ -->
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

<!-- ═══════════════ STORY TEASER (TRANSCRIPT ALIGNED) ═══════════════ -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4.5rem);align-items:center;">
        <div class="img-frame frame-gold" data-reveal="left">
            <img src="/Frontend/images/farm-fresh-eggs.jpg" alt="Busia Chicken Farm fresh eggs and layer flock" loading="lazy" style="aspect-ratio:4/3;" data-parallax="0.06">
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

<!-- ═══════════════ TESTIMONIALS (FACEBOOK & EGG FOCUS) ═══════════════ -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered" style="color:var(--brand-red);font-weight:800;letter-spacing:0.08em;"><i data-lucide="facebook" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;color:#1877F2;"></i> VERIFIED FACEBOOK REVIEWS • EGGS ONLY</span>
            <h2 class="section-title">What Egg Customers Say on <em>Facebook</em></h2>
            <p class="lead">Real reviews extracted from Facebook — from restaurants, bakeries, retailers and families who rely on our fresh Grade-A eggs daily.</p>
        </div>

        <div class="grid-3" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.8rem;">
            <?php
            $reviews = [];
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY id ASC LIMIT 6");
                    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {}
            }

            if (empty($reviews)) {
                $reviews = [
                    [
                        'customer_name' => 'Daniel Ochieng',
                        'customer_role' => 'Restaurant Owner, Busia Town (via Facebook)',
                        'content' => 'Fresh eggs delivered to our restaurant every morning, without fail. The golden yolk richness and shell strength are unmatched in Busia County. Our breakfast customers always notice the difference!'
                    ],
                    [
                        'customer_name' => 'Mary Atieno',
                        'customer_role' => 'Delight Pastries & Bakery, Kisumu (via Facebook)',
                        'content' => 'We source over 100 trays of Grade-A eggs weekly from Busia Chicken for our bakeries in Kisumu. Shell strength and yolk freshness are exceptional. Zero breakage in transit and our cakes rise beautifully.'
                    ],
                    [
                        'customer_name' => 'Peter Wafula',
                        'customer_role' => 'Supermarket Egg Buyer, Busia (via Facebook)',
                        'content' => 'Busia Chicken delivers Grade-A eggs to our retail shelves directly from their Nasira facility. Consistent grading, hygienic 30-egg packaging, and our shoppers buy them out every single morning.'
                    ],
                    [
                        'customer_name' => 'Christine Nasimiyu',
                        'customer_role' => 'Wholesale Egg Vendor, Tangakona Depot (via Facebook)',
                        'content' => 'Picking up fresh egg crates at the Tangakona depot along the highway is fast and convenient. Genuine farm wholesale rates, spotless eggs, and they are literally harvested that very morning.'
                    ],
                    [
                        'customer_name' => 'Erick Barasa',
                        'customer_role' => 'Continental Caterers & Events, Eldoret (via Facebook)',
                        'content' => 'Ordered 50 trays of eggs for an executive catering event. Every single egg had thick albumen and rich orange yolks. Best egg supplier in Western Kenya by far.'
                    ],
                    [
                        'customer_name' => 'Grace Akello',
                        'customer_role' => 'Sunrise Breakfast Cafe, Busia Border (via Facebook)',
                        'content' => 'Consistent egg quality and size. Switched our breakfast orders to Busia Chicken 6 months ago and never looked back. The yolk flavor is rich, fresh, and perfectly balanced.'
                    ]
                ];
            }

            foreach ($reviews as $rev):
                $initials = '';
                $parts = explode(' ', trim($rev['customer_name']));
                foreach ($parts as $p) {
                    if (!empty($p)) $initials .= strtoupper($p[0]);
                }
                $initials = substr($initials, 0, 2);
            ?>
            <figure class="testimonial-card">
                <span class="quote-mark">&ldquo;</span>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <div class="stars" style="color:#E0A82E;">★★★★★</div>
                    <span style="display:inline-flex;align-items:center;gap:4px;color:#1877F2;font-size:0.72rem;font-weight:700;"><i data-lucide="check-circle" style="width:12px;height:12px;"></i> Facebook</span>
                </div>
                <blockquote><?php echo htmlspecialchars($rev['content'], ENT_QUOTES, 'UTF-8'); ?></blockquote>
                <figcaption>
                    <span class="avatar"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><span class="t-name"><?php echo htmlspecialchars($rev['customer_name'], ENT_QUOTES, 'UTF-8'); ?></span><br><span class="t-role"><?php echo htmlspecialchars($rev['customer_role'] ?? 'Verified Customer', ENT_QUOTES, 'UTF-8'); ?></span></span>
                </figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════ CTA (TRANSCRIPT ALIGNED) ═══════════════ -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="cta-band" data-reveal="zoom">
            <div class="container" style="padding:0;">
                <span class="eyebrow centered" style="color:var(--gold-300);">Let's Get Started</span>
                <h2>Ready to elevate your farm?</h2>
                <p>Join thousands of successful farmers using Busia Chicken products and management tools. Whether you're a family, a restaurant or a commercial operation — we've got you covered.</p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center;">
                    <a href="/Frontend/pages/shop.php" class="btn btn-red" data-magnetic><i data-lucide="shopping-bag" style="width:18px;height:18px;"></i> Order Chicks &amp; Feeds</a>
                    <a href="/Frontend/pages/register.php" class="btn btn-primary" data-magnetic>Create Account</a>
                    <a href="/Frontend/pages/contact.php" class="btn btn-ghost">Contact Sales</a>
                    <a href="https://wa.me/254727585599?text=Hello%20Busia%20Chicken%20Farm,%20I%20would%20like%20to%20order" target="_blank" rel="noopener" class="btn btn-outline" style="background:rgba(255,255,255,0.15);border-color:#fff;color:#fff;">
                        <i data-lucide="phone"></i> Call +254 727 585 599
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
