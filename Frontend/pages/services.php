<?php
/**
 * Our Services — Busia Chicken Limited
 * All content is managed in Admin → Website CMS → Services.
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Our Services - Comprehensive Poultry & Agricultural Solutions | Busia Chicken Limited';

include __DIR__ . '/../includes/header.php';

$serviceIcons = ['egg', 'leaf', 'sprout', 'wheat', 'graduation-cap', 'shield-check'];
$serviceRows = cms_rows('services', 'svc', 6, ['kicker', 'title', 'text', 'button', 'link']);
$stepRows = cms_rows('services', 'step', 4, ['title', 'text']);
$farmPhone = (string)getSetting('farm_phone', '+254 727 585 599');
$whatsappDigits = preg_replace('/\D+/', '', (string)getSetting('farm_whatsapp', '254727585599')) ?: '254727585599';
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('<?php echo htmlspecialchars(cms_img('services', 'hero_image', '/Frontend/images/adbg.webp'), ENT_QUOTES, 'UTF-8'); ?>');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>Services</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100"><?php echo cms_html('services', 'hero_title'); ?></h1>
        <p data-reveal="fade" data-reveal-delay="200"><?php echo cms_text('services', 'hero_subtitle'); ?></p>
    </div>
</section>

<!-- Introduction & Core Services -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">OUR SERVICES</span>
            <h2 class="section-title"><?php echo cms_html('services', 'intro_title'); ?></h2>
            <p class="lead" style="max-width:880px;margin:0 auto;"><?php echo cms_text('services', 'intro_lead'); ?></p>
        </div>

        <div class="grid-3" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:2rem;margin-top:2.5rem;">
            <?php foreach ($serviceRows as $i => $service): ?>
            <div class="p-card" style="display:flex;flex-direction:column;">
                <div class="p-icon"><i data-lucide="<?php echo $serviceIcons[$i] ?? 'check-circle'; ?>" style="width:28px;height:28px;"></i></div>
                <span style="font-weight:800;color:var(--gold-ink);font-size:0.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;"><?php echo htmlspecialchars((string)$service['kicker'], ENT_QUOTES); ?></span>
                <h3 style="margin-bottom:0.8rem;"><?php echo htmlspecialchars((string)$service['title'], ENT_QUOTES); ?></h3>
                <p style="flex-grow:1;color:#475569;line-height:1.65;"><?php echo htmlspecialchars((string)$service['text'], ENT_QUOTES); ?></p>
                <div style="margin-top:1.2rem;padding-top:1rem;border-top:1px solid #f1f5f9;">
                    <a href="<?php echo htmlspecialchars(cms('services', 'svc_' . ($i + 1) . '_link', '/Frontend/pages/contact.php'), ENT_QUOTES); ?>" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;"><?php echo htmlspecialchars((string)$service['button'], ENT_QUOTES); ?></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Statistics Band -->
<section class="section-pad stats-band">
    <div class="container">
        <div class="grid-3" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:2rem;text-align:center;">
            <?php foreach (cms_rows('services', 'stats', 3, ['value', 'suffix', 'label']) as $i => $stat): ?>
            <div class="stat-item" data-reveal <?php echo $i > 0 ? 'data-reveal-delay="' . ($i * 120) . '"' : ''; ?>>
                <b><span data-counter="<?php echo preg_replace('/[^0-9]/', '', (string)$stat['value']); ?>" data-suffix="<?php echo htmlspecialchars((string)$stat['suffix'], ENT_QUOTES); ?>">0</span></b>
                <span><?php echo htmlspecialchars((string)$stat['label'], ENT_QUOTES); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How We Help -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered" style="color:var(--gold-ink);font-weight:800;letter-spacing:0.08em;"><i data-lucide="check-circle" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;"></i> <?php echo cms_text('services', 'process_eyebrow'); ?></span>
            <h2 class="section-title"><?php echo cms_html('services', 'process_title'); ?></h2>
            <p class="lead"><?php echo cms_text('services', 'process_lead'); ?></p>
        </div>

        <div class="grid-4" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.8rem;">
            <?php foreach ($stepRows as $i => $step): ?>
            <div class="p-card num-card">
                <span class="step-no"><?php echo str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT); ?></span>
                <h3><?php echo htmlspecialchars((string)$step['title'], ENT_QUOTES); ?></h3>
                <p><?php echo htmlspecialchars((string)$step['text'], ENT_QUOTES); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- In-Person Support & CTA -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="cta-band" data-reveal="zoom">
            <div class="container" style="padding:0;">
                <span class="eyebrow centered" style="color:var(--gold-300);"><?php echo cms_text('services', 'cta_eyebrow'); ?></span>
                <h2><?php echo cms_text('services', 'cta_title'); ?></h2>
                <p><?php echo cms_text('services', 'cta_text'); ?></p>
                <div style="display:flex;gap:1.2rem;flex-wrap:wrap;justify-content:center;margin-top:1.5rem;">
                    <a href="/Frontend/pages/contact.php" class="btn btn-primary" data-magnetic>Book Consultation</a>
                    <a href="tel:+<?php echo htmlspecialchars($whatsappDigits, ENT_QUOTES); ?>" class="btn btn-ghost" style="display:inline-flex;align-items:center;gap:8px;">
                        <i data-lucide="phone"></i> Call: <?php echo htmlspecialchars($farmPhone, ENT_QUOTES); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>
