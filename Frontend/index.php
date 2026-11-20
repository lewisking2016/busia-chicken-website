<?php
/**
 * Homepage — Busia Chicken Limited
 * The Chicken People
 *
 * All copy and imagery on this page is served from the database through the
 * CMS (Admin → Website CMS → Homepage). Nothing here is hard-coded: the
 * arguments below are only last-resort defaults if a row is missing.
 */
declare(strict_types=1);

$page_title = 'Busia Chicken Limited - The Chicken People | Quality Poultry for East Africa';
include 'includes/header.php';
require_once __DIR__ . '/includes/product_source.php';

$pdo = function_exists('getDB') ? getDB() : (function_exists('getDatabaseConnection') ? getDatabaseConnection() : null);

// ── Hero slides (Admin → Website CMS → Hero Sliders) ──────────────
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
            'subtitle' => 'Farm-fresh eggs, quality layer feeds and organic manure — produced with care at our Busia farms and delivered with confidence.',
            'cta_primary_text' => 'Explore Products',
            'cta_primary_link' => '/Frontend/pages/shop.php',
            'cta_secondary_text' => 'Our Story',
            'cta_secondary_link' => '/Frontend/pages/about.php',
            'stat1_val' => '100', 'stat1_suffix' => 'k', 'stat1_label' => 'Chickens raised annually',
            'stat2_val' => '5000', 'stat2_suffix' => '+', 'stat2_label' => 'Happy customers',
            'stat3_val' => '13', 'stat3_suffix' => '+', 'stat3_label' => 'Years in business',
            'image_url' => '/Frontend/images/adbg.webp'
        ],
    ];
}

// ── CMS helpers for this page ─────────────────────────────────────
$marqueeWords = cms_lines('home', 'marquee_list', [
    'BUSIA CHICKEN LIMITED', 'THE CHICKEN PEOPLE', 'NASIRA FARM HQ', 'TAKGAKONA DEPOT',
]);
$whyRows = cms_rows('home', 'why', 6, ['title', 'text']);
$whyIcons = ['shield-check', 'truck', 'graduation-cap', 'egg', 'dna', 'heart-handshake'];
$statRows = cms_rows('home', 'stats', 4, ['value', 'suffix', 'label']);
$stepRows = cms_rows('home', 'step', 4, ['title', 'text']);
$storyBullets = cms_lines('home', 'story_bullets');
$whatsappDigits = preg_replace('/\D+/', '', (string)getSetting('farm_whatsapp', '254727585599')) ?: '254727585599';
$farmPhone = (string)getSetting('farm_phone', '+254 727 585 599');
?>

<!-- ═══════════════ HERO SLIDER (CMS-POWERED) ═══════════════ -->
<section class="hero-swiper" aria-label="Featured highlights">
    <div class="swiper-wrapper">
        <?php foreach ($slides as $idx => $s): ?>
        <div class="swiper-slide hero-slide">
            <img src="<?php echo htmlspecialchars(optimizedSiteImage((string)$s['image_url']), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($s['title_main'] . ' ' . $s['title_em'], ENT_QUOTES, 'UTF-8'); ?>" width="1600" height="900" decoding="async" <?php echo $idx === 0 ? 'fetchpriority="high" loading="eager"' : 'loading="lazy"'; ?>>
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
        <?php for ($copy = 0; $copy < 2; $copy++): foreach ($marqueeWords as $w): ?>
            <span><?php echo htmlspecialchars($w, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endforeach; endfor; ?>
    </div>
</div>

<!-- ═══════════════ WHY CHOOSE US ═══════════════ -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered"><?php echo cms_text('home', 'why_eyebrow'); ?></span>
            <h2 class="section-title"><?php echo cms_html('home', 'why_title'); ?></h2>
            <p class="lead"><?php echo cms_text('home', 'why_lead'); ?></p>
        </div>

        <div class="grid-3" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.8rem;">
            <?php foreach ($whyRows as $i => $row): ?>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="<?php echo $whyIcons[$i] ?? 'check-circle'; ?>" style="width:28px;height:28px;"></i></div>
                <h3><?php echo cms_text('home', 'why_' . ($i + 1) . '_title'); ?></h3>
                <p><?php echo cms_text('home', 'why_' . ($i + 1) . '_text'); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════ STATS BAND ═══════════════ -->
<section class="section-pad stats-band">
    <div class="container">
        <div class="grid-4" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.6rem;">
            <?php foreach ($statRows as $i => $row): ?>
            <div class="stat-item" data-reveal <?php echo $i > 0 ? 'data-reveal-delay="' . ($i * 120) . '"' : ''; ?>>
                <b><span data-counter="<?php echo preg_replace('/[^0-9]/', '', (string)$row['value']); ?>" data-suffix="<?php echo htmlspecialchars((string)$row['suffix'], ENT_QUOTES, 'UTF-8'); ?>">0</span></b>
                <span><?php echo htmlspecialchars((string)$row['label'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════ BEST SELLERS / FEATURED PRODUCTS ═══════════════ -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:1.6rem;flex-wrap:wrap;margin-bottom:2.4rem;" data-reveal>
            <div>
                <span class="eyebrow" style="color:var(--gold-ink);font-weight:800;letter-spacing:0.08em;"><i data-lucide="flame" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;"></i> <?php echo cms_text('home', 'products_eyebrow'); ?></span>
                <h2 class="section-title" style="margin-bottom:0.4rem;"><?php echo cms_html('home', 'products_title'); ?></h2>
                <p class="lead" style="margin:0;"><?php echo cms_text('home', 'products_lead'); ?></p>
            </div>
            <a href="/Frontend/pages/shop.php" class="btn btn-red">View Full Shop <i data-lucide="arrow-right" style="width:16px;height:16px;"></i></a>
        </div>

        <div style="position:relative;padding:0 6px;">
            <div class="swiper creative-slider swiper-products">
                <div class="swiper-wrapper">
                <?php
                // Featured products come only from the products table (Admin → Products).
                $displayProducts = loadFeaturedProducts($pdo, 8);

                foreach ($displayProducts as $index => $product):
                    $img = $product['image_url'] ?? $product['img'] ?? '';
                    if (!$img) {
                        $type = $product['product_type'] ?? 'feed';
                        $img = cms_product_image((string)$type);
                    }
                ?>
                <div class="swiper-slide">
                    <div class="product-card creative-card" data-id="<?php echo $product['id']; ?>" data-type="<?php echo htmlspecialchars($product['product_type'] ?? '', ENT_QUOTES); ?>" data-instock="<?php echo (!empty($product['stock_quantity']) && $product['stock_quantity'] > 0) ? '1' : '0'; ?>">
                        <a href="/Frontend/pages/product-detail.php?id=<?php echo $product['id']; ?>" style="display:block;text-decoration:none;color:inherit;">
                            <div class="product-image">
                                <span class="product-badge badge-red">Best Seller</span>
                                <img src="<?php echo htmlspecialchars(optimizedSiteImage($img), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" width="800" height="600" loading="lazy" decoding="async">
                            </div>
                        </a>
                        <div class="product-body">
                            <h4 class="product-name"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="product-description"><?php echo htmlspecialchars($product['description'] ?? 'Premium quality poultry product.'); ?></p>
                            <div class="product-meta" style="display:flex;justify-content:space-between;align-items:center;">
                                <span class="product-price"><?php echo htmlspecialchars(currency_label()); ?> <?php echo number_format((float)$product['price'], 0); ?></span>
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

<!-- ═══════════════ TWO LOCATIONS ═══════════════ -->
<section class="locations-section" id="locations-showcase">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered"><?php echo cms_text('home', 'locations_eyebrow'); ?></span>
            <h2 class="section-title"><?php echo cms_html('home', 'locations_title'); ?></h2>
            <p class="lead"><?php echo cms_text('home', 'locations_lead'); ?></p>
        </div>

        <div class="locations-grid" data-reveal-group>
            <div class="location-card">
                <span class="location-badge"><i data-lucide="building"></i> <?php echo cms_text('home', 'loc_1_badge'); ?></span>
                <h3 class="location-title"><?php echo cms_text('home', 'loc_1_title'); ?></h3>
                <p class="location-desc"><?php echo cms_text('home', 'loc_1_desc'); ?></p>
                <div class="location-meta">
                    <div class="location-meta-item">
                        <i data-lucide="map-pin"></i>
                        <span><?php echo cms_text('home', 'loc_1_address'); ?></span>
                    </div>
                    <div class="location-meta-item">
                        <i data-lucide="clock"></i>
                        <span><?php echo cms_text('home', 'loc_1_hours'); ?></span>
                    </div>
                    <div class="location-meta-item">
                        <i data-lucide="phone"></i>
                        <a href="tel:+<?php echo htmlspecialchars($whatsappDigits, ENT_QUOTES); ?>" style="color:var(--brand-700);font-weight:700;"><?php echo htmlspecialchars($farmPhone, ENT_QUOTES); ?></a>
                    </div>
                </div>
                <div class="location-actions">
                    <a href="<?php echo htmlspecialchars(cms('home', 'loc_1_maps', 'https://www.google.com/maps/place/0%C2%B026\'06.0%22N+34%C2%B012\'07.6%22E/@0.4349969,34.1995236,17z/data=!3m1!4b1!4m4!3m3!8m2!3d0.4349969!4d34.2020985?hl=en&entry=ttu&g_ep=EgoyMDI2MTExNi4wIKXMDSoASAFQAw%3D%3D'), ENT_QUOTES); ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                        <i data-lucide="navigation"></i> Google Maps
                    </a>
                    <a href="https://wa.me/<?php echo htmlspecialchars($whatsappDigits, ENT_QUOTES); ?>?text=<?php echo rawurlencode('Hello Busia Chicken Farm, I would like to visit the Nasira Farm'); ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                        <i data-lucide="message-square"></i> Inquire HQ
                    </a>
                </div>
            </div>

            <div class="location-card">
                <span class="location-badge hub"><i data-lucide="store"></i> <?php echo cms_text('home', 'loc_2_badge'); ?></span>
                <h3 class="location-title"><?php echo cms_text('home', 'loc_2_title'); ?></h3>
                <p class="location-desc"><?php echo cms_text('home', 'loc_2_desc'); ?></p>
                <div class="location-meta">
                    <div class="location-meta-item">
                        <i data-lucide="map-pin"></i>
                        <span><?php echo cms_text('home', 'loc_2_address'); ?></span>
                    </div>
                    <div class="location-meta-item">
                        <i data-lucide="clock"></i>
                        <span><?php echo cms_text('home', 'loc_2_hours'); ?></span>
                    </div>
                    <div class="location-meta-item">
                        <i data-lucide="phone"></i>
                        <a href="tel:+<?php echo htmlspecialchars($whatsappDigits, ENT_QUOTES); ?>" style="color:var(--brand-700);font-weight:700;"><?php echo htmlspecialchars($farmPhone, ENT_QUOTES); ?></a>
                    </div>
                </div>
                <div class="location-actions">
                    <a href="<?php echo htmlspecialchars(cms('home', 'loc_2_maps', 'https://www.google.com/maps?q=0.468734,34.2188207&z=17&hl=en'), ENT_QUOTES); ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                        <i data-lucide="navigation"></i> Google Maps
                    </a>
                    <a href="https://wa.me/<?php echo htmlspecialchars($whatsappDigits, ENT_QUOTES); ?>?text=<?php echo rawurlencode('Hello Busia Chicken Farm, I would like to pick up supplies at Tangakona Farm'); ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                        <i data-lucide="shopping-bag"></i> Order from Farm
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ PROCESS ═══════════════ -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered"><?php echo cms_text('home', 'process_eyebrow'); ?></span>
            <h2 class="section-title"><?php echo cms_html('home', 'process_title'); ?></h2>
            <p class="lead"><?php echo cms_text('home', 'process_lead'); ?></p>
        </div>

        <div class="grid-4" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1.8rem;">
            <?php foreach ($stepRows as $i => $row): ?>
            <div class="p-card num-card">
                <span class="step-no"><?php echo str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT); ?></span>
                <h3><?php echo cms_html('home', 'step_' . ($i + 1) . '_title'); ?></h3>
                <p><?php echo cms_text('home', 'step_' . ($i + 1) . '_text'); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════ STORY TEASER ═══════════════ -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4.5rem);align-items:center;">
        <div class="img-frame frame-gold" data-reveal="left">
            <img src="<?php echo htmlspecialchars(cms_img('home', 'story_image', '/Frontend/images/farm-fresh-eggs.webp'), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo cms_text('home', 'story_title', 'Busia Chicken Farm'); ?>" width="800" height="600" loading="lazy" decoding="async" style="aspect-ratio:4/3;" data-parallax="0.06">
        </div>
        <div data-reveal="right">
            <span class="eyebrow"><?php echo cms_text('home', 'story_eyebrow'); ?></span>
            <h2 class="section-title"><?php echo cms_html('home', 'story_title'); ?></h2>
            <p class="lead"><?php echo cms_text('home', 'story_text'); ?></p>
            <ul class="check-list" style="margin:1.6rem 0 2.2rem;">
                <?php foreach ($storyBullets as $bullet): ?>
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg><?php echo htmlspecialchars($bullet, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <a href="<?php echo htmlspecialchars(cms('home', 'story_cta_primary_link', '/Frontend/pages/about.php'), ENT_QUOTES); ?>" class="btn btn-primary" data-magnetic><?php echo cms_text('home', 'story_cta_primary_text'); ?></a>
                <a href="<?php echo htmlspecialchars(cms('home', 'story_cta_secondary_link', '/Frontend/pages/services.php'), ENT_QUOTES); ?>" class="btn btn-outline"><?php echo cms_text('home', 'story_cta_secondary_text'); ?></a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ TESTIMONIALS ═══════════════ -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered" style="color:var(--gold-ink);font-weight:800;letter-spacing:0.08em;"><i data-lucide="facebook" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;color:#1877F2;"></i> <?php echo cms_text('home', 'testimonials_eyebrow'); ?></span>
            <h2 class="section-title"><?php echo cms_html('home', 'testimonials_title'); ?></h2>
            <p class="lead"><?php echo cms_text('home', 'testimonials_lead'); ?></p>
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

            foreach ($reviews as $rev):
                $initials = '';
                foreach (explode(' ', trim((string)$rev['customer_name'])) as $p) {
                    if ($p !== '') $initials .= strtoupper($p[0]);
                }
                $initials = substr($initials, 0, 2);
            ?>
            <figure class="testimonial-card">
                <span class="quote-mark">&ldquo;</span>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <div class="stars" style="color:#E0A82E;"><?php echo str_repeat('★', max(1, min(5, (int)($rev['rating'] ?? 5)))); ?></div>
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

<!-- ═══════════════ CTA ═══════════════ -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="cta-band" data-reveal="zoom">
            <div class="container" style="padding:0;">
                <span class="eyebrow centered" style="color:var(--gold-300);"><?php echo cms_text('home', 'cta_eyebrow'); ?></span>
                <h2><?php echo cms_text('home', 'cta_title'); ?></h2>
                <p><?php echo cms_text('home', 'cta_text'); ?></p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center;">
                    <a href="<?php echo htmlspecialchars(cms('home', 'cta_button1_link', '/Frontend/pages/shop.php'), ENT_QUOTES); ?>" class="btn btn-red" data-magnetic><i data-lucide="shopping-bag" style="width:18px;height:18px;"></i> <?php echo cms_text('home', 'cta_button1_text'); ?></a>
                    <a href="<?php echo htmlspecialchars(cms('home', 'cta_button2_link', '/Frontend/pages/register.php'), ENT_QUOTES); ?>" class="btn btn-primary" data-magnetic><?php echo cms_text('home', 'cta_button2_text'); ?></a>
                    <a href="<?php echo htmlspecialchars(cms('home', 'cta_button3_link', '/Frontend/pages/contact.php'), ENT_QUOTES); ?>" class="btn btn-ghost"><?php echo cms_text('home', 'cta_button3_text'); ?></a>
                    <a href="https://wa.me/<?php echo htmlspecialchars($whatsappDigits, ENT_QUOTES); ?>?text=<?php echo rawurlencode('Hello Busia Chicken Farm, I would like to order'); ?>" target="_blank" rel="noopener" class="btn btn-outline" style="background:rgba(255,255,255,0.15);border-color:#fff;color:#fff;">
                        <i data-lucide="phone"></i> Call <?php echo htmlspecialchars($farmPhone, ENT_QUOTES); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
