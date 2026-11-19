<?php
/**
 * About Us Page — Busia Chicken Limited
 * The Chicken People
 * Transcript aligned: 2015 origins with 300 birds, Core Values, Certifications, Two Facilities (Nasira & Tangakona)
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'About Us - Busia Chicken Limited | The Chicken People';

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('<?php echo htmlspecialchars(cms_img('about', 'hero_image', '/Frontend/images/download-2.webp'), ENT_QUOTES, 'UTF-8'); ?>');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>About</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100"><?php echo cms_html('about', 'hero_title'); ?></h1>
        <p data-reveal="fade" data-reveal-delay="200"><?php echo cms_text('about', 'hero_subtitle'); ?></p>
    </div>
</section>

<!-- Company Story (Exact Transcript) -->
<section class="section-pad bg-white">
    <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4.5rem);align-items:center;">
        <div data-reveal="left">
            <span class="eyebrow">Our Story</span>
            <h2 class="section-title"><?php echo cms_html('about', 'story_title'); ?></h2>
            <p class="lead"><?php echo cms_text('about', 'story_p1'); ?></p>
            <p class="lead"><?php echo cms_text('about', 'story_p2'); ?></p>
            <ul class="check-list" style="margin-top:1.8rem;">
                <?php foreach (cms_lines('about', 'story_bullets') as $bullet): ?>
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg><?php echo htmlspecialchars($bullet, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div data-reveal="right">
            <div class="img-frame frame-gold">
                <img src="<?php echo htmlspecialchars(cms_img('about', 'story_image', '/Frontend/images/download-5.webp'), ENT_QUOTES, 'UTF-8'); ?>" alt="Busia Chicken Farm automated egg sorting and grading facility" width="800" height="600" loading="lazy" decoding="async" style="aspect-ratio:4/3;" data-parallax="0.05">
            </div>
        </div>
    </div>
</section>

<!-- Stats Band -->
<section class="section-pad stats-band">
    <div class="container">
        <div class="grid-4" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.6rem;">
            <?php foreach (cms_rows('about', 'stats', 4, ['value', 'suffix', 'label']) as $i => $stat): ?>
            <div class="stat-item" data-reveal <?php echo $i > 0 ? 'data-reveal-delay="' . ($i * 120) . '"' : ''; ?>>
                <b><span data-counter="<?php echo preg_replace('/[^0-9]/', '', (string)$stat['value']); ?>" data-suffix="<?php echo htmlspecialchars((string)$stat['suffix'], ENT_QUOTES); ?>">0</span></b>
                <span><?php echo htmlspecialchars((string)$stat['label'], ENT_QUOTES); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Core Values (Exact Transcript) -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered" style="color:var(--gold-ink);font-weight:800;letter-spacing:0.08em;"><i data-lucide="shield" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;"></i> OUR GUIDING PRINCIPLES</span>
            <h2 class="section-title"><?php echo cms_html('about', 'values_title'); ?></h2>
        </div>
        <div class="grid-3" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.8rem;">
            <?php $valueIcons = ['badge-check', 'leaf', 'users']; foreach (cms_rows('about', 'value', 3, ['title', 'text']) as $i => $value): ?>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="<?php echo $valueIcons[$i] ?? 'check-circle'; ?>" style="width:28px;height:28px;"></i></div>
                <h3><?php echo htmlspecialchars((string)$value['title'], ENT_QUOTES); ?></h3>
                <p><?php echo htmlspecialchars((string)$value['text'], ENT_QUOTES); ?></p>
            </div>
            <?php endforeach; ?>
    </div>
</section>

<!-- Certifications (Exact Transcript) -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Certifications</span>
            <h2 class="section-title"><?php echo cms_html('about', 'certs_title'); ?></h2>
        </div>
        <div class="grid-4" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.6rem;">
            <?php $certIcons = ['award', 'file-check', 'shield-check', 'receipt']; foreach (cms_rows('about', 'cert', 4, ['title', 'text']) as $i => $cert): ?>
            <div class="p-card" style="text-align:center;">
                <div class="p-icon" style="margin:0 auto 1.2rem;"><i data-lucide="<?php echo $certIcons[$i] ?? 'award'; ?>" style="width:28px;height:28px;"></i></div>
                <h3 style="font-size:1.1rem;"><?php echo htmlspecialchars((string)$cert['title'], ENT_QUOTES); ?></h3>
                <p style="font-size:0.9rem;"><?php echo htmlspecialchars((string)$cert['text'], ENT_QUOTES); ?></p>
            </div>
            <?php endforeach; ?>
    </div>
</section>

<!-- Two Operating Locations (Google Business) -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Our Footprint</span>
            <h2 class="section-title"><?php echo cms_html('about', 'locations_title'); ?></h2>
            <p class="lead"><?php echo cms_text('about', 'locations_lead'); ?></p>
        </div>

        <div class="locations-grid" data-reveal-group>
            <div class="location-card">
                <span class="location-badge"><i data-lucide="building"></i> Nasira Farm &amp; Hatchery</span>
                <h3 class="location-title">Nasira AC Sub-location</h3>
                <p class="location-desc">Our main layer farm: brooding units and layer houses run under strict biosecurity, with eggs collected and graded here every day.</p>
                <div class="location-meta">
                    <div class="location-meta-item">
                        <i data-lucide="map-pin"></i>
                        <span>Nasira AC Sub-location, Busibwabo, Busia County, Kenya</span>
                    </div>
                    <div class="location-meta-item">
                        <i data-lucide="phone"></i>
                        <a href="tel:+254727585599" style="color:var(--brand-700);font-weight:700;">+254 727 585 599</a>
                    </div>
                </div>
            </div>

            <div class="location-card">
                <span class="location-badge hub"><i data-lucide="store"></i> Tangakona Farm</span>
                <h3 class="location-title">Tangakona Farm</h3>
                <p class="location-desc">Our second farm on the Busia-Kisumu Highway, with layer houses, feed milling and a collection point. Farmers pick up fresh eggs, layer feeds and organic manure without having to navigate deep rural access roads.</p>
                <div class="location-meta">
                    <div class="location-meta-item">
                        <i data-lucide="map-pin"></i>
                        <span>Tangakona Junction / Market along Busia-Kisumu Highway, Kenya</span>
                    </div>
                    <div class="location-meta-item">
                        <i data-lucide="phone"></i>
                        <a href="tel:+254727585599" style="color:var(--brand-700);font-weight:700;">+254 727 585 599</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="cta-band" data-reveal="zoom">
            <div class="container" style="padding:0;">
                <span class="eyebrow centered" style="color:var(--gold-300);">Let's Work Together</span>
                <h2>Ready to partner with us?</h2>
                <p>Whether you're a commercial farm, a retailer or a family looking for quality poultry products — we're here to help.</p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center;">
                    <a href="contact.php" class="btn btn-primary" data-magnetic>Contact Us</a>
                    <a href="shop.php" class="btn btn-ghost">Shop Now</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>
