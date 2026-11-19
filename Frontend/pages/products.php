<?php
/**
 * Products Overview Page — Busia Chicken Limited
 *
 * Copy, images and bullet lists are managed in Admin → Website CMS → Products.
 * The catalogue itself (names, prices, stock, photos) lives in the products
 * table and is managed under Admin → Products.
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Our Products - Fresh Eggs, Layer Feeds & Organic Fertiliser | Busia Chicken Limited';

include __DIR__ . '/../includes/header.php';

$whatsappDigits = preg_replace('/\D+/', '', (string)getSetting('farm_whatsapp', '254727585599')) ?: '254727585599';

// The farm sells three things right now: eggs, layer feeds, organic fertiliser.
$productSections = [
    [
        'key' => 'eggs',
        'eyebrow' => 'FRESH EGGS',
        'icon' => 'egg',
        'primary_text' => 'Shop Fresh Eggs',
        'primary_link' => '/Frontend/pages/shop.php',
        'order' => 'normal',
    ],
    [
        'key' => 'feeds',
        'eyebrow' => 'LAYER FEEDS',
        'icon' => 'wheat',
        'primary_text' => 'Shop Layer Feeds',
        'primary_link' => '/Frontend/pages/shop.php',
        'order' => 'reversed',
    ],
    [
        'key' => 'organic',
        'eyebrow' => 'ORGANIC FERTILISER',
        'icon' => 'sprout',
        'primary_text' => 'Shop Organic Manure & Folia',
        'primary_link' => '/Frontend/pages/shop.php',
        'order' => 'normal',
    ],
];
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('<?php echo htmlspecialchars(cms_img('products', 'hero_image', '/Frontend/images/adbg.webp'), ENT_QUOTES, 'UTF-8'); ?>');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>Products</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100"><?php echo cms_html('products', 'hero_title'); ?></h1>
        <p data-reveal="fade" data-reveal-delay="200"><?php echo cms_text('products', 'hero_subtitle'); ?></p>
    </div>
</section>

<?php $sectionCount = count($productSections); foreach ($productSections as $sectionIndex => $section): $key = $section['key']; ?>
<section class="section-pad bg-white">
    <div class="container">
        <div class="row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4.5rem);align-items:center;">
            <div data-reveal="<?php echo $section['order'] === 'reversed' ? 'right' : 'left'; ?>" style="<?php echo $section['order'] === 'reversed' ? 'order:2;' : ''; ?>">
                <div class="img-frame frame-gold">
                    <img src="<?php echo htmlspecialchars(cms_img('products', $key . '_image', '/Frontend/images/adbg.webp'), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo cms_text('products', $key . '_title'); ?>" width="800" height="600" loading="lazy" decoding="async" style="aspect-ratio:4/3;" data-parallax="0.05">
                </div>
            </div>
            <div data-reveal="<?php echo $section['order'] === 'reversed' ? 'left' : 'right'; ?>" style="<?php echo $section['order'] === 'reversed' ? 'order:1;' : ''; ?>">
                <span class="eyebrow" style="color:var(--gold-ink);font-weight:800;letter-spacing:0.08em;"><i data-lucide="<?php echo $section['icon']; ?>" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;"></i> <?php echo htmlspecialchars($section['eyebrow'], ENT_QUOTES); ?></span>
                <h2 class="section-title"><?php echo cms_html('products', $key . '_title'); ?></h2>
                <p class="lead"><?php echo cms_text('products', $key . '_text'); ?></p>
                <ul class="check-list" style="margin:1.6rem 0 2.2rem;">
                    <?php foreach (cms_lines('products', $key . '_bullets') as $bullet): ?>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg><?php echo htmlspecialchars($bullet, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a href="<?php echo htmlspecialchars($section['primary_link'], ENT_QUOTES); ?>" class="btn btn-primary" data-magnetic><?php echo htmlspecialchars($section['primary_text'], ENT_QUOTES); ?></a>
                    <a href="https://wa.me/<?php echo htmlspecialchars($whatsappDigits, ENT_QUOTES); ?>?text=<?php echo rawurlencode('Hello Busia Chicken Farm, I would like to order ' . strip_tags(cms('products', $key . '_title', ''))); ?>" class="btn btn-red" target="_blank" rel="noopener"><i data-lucide="phone"></i> Quick Order (WhatsApp)</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($sectionIndex < $sectionCount - 1): ?><hr class="ornament-line"><?php endif; ?>
<?php endforeach; ?>

<!-- Bulk Orders -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="cta-band" data-reveal="zoom">
            <div class="container" style="padding:0;">
                <span class="eyebrow centered" style="color:var(--gold-300);"><?php echo cms_text('products', 'bulk_eyebrow'); ?></span>
                <h2><?php echo cms_text('products', 'bulk_title'); ?></h2>
                <p><?php echo cms_text('products', 'bulk_text'); ?></p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center;margin-top:1.5rem;">
                    <a href="/Frontend/pages/contact.php?subject=Bulk+Order+Quote" class="btn btn-primary" data-magnetic>Request Bulk Quote</a>
                    <a href="https://wa.me/<?php echo htmlspecialchars($whatsappDigits, ENT_QUOTES); ?>?text=<?php echo rawurlencode('Hello Busia Chicken Farm, I would like to request a Bulk Order Quote'); ?>" class="btn btn-red" target="_blank" rel="noopener"><i data-lucide="phone"></i> WhatsApp Wholesale Desk</a>
                    <a href="/Frontend/pages/shop.php" class="btn btn-ghost">Browse the Shop</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>
