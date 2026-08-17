<?php
/**
 * Products Page — Premium Redesign
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Products - Chicken & Feeds | Busia Chicken Farm';

include '../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('/Frontend/images/adbg.png');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>Products</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100">Our Premium <em>Products</em></h1>
        <p data-reveal="fade" data-reveal-delay="200">Discover our range of high-quality chicken products and specially formulated animal feeds designed for optimal growth and productivity.</p>
    </div>
</section>

<!-- Broilers -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4.5rem);align-items:center;">
            <div data-reveal="left">
                <span class="eyebrow">Meat Production</span>
                <h2 class="section-title">Broiler <em>Chickens</em></h2>
                <p class="lead">High-quality broilers bred for superior meat yield and rapid growth cycles — vaccinated, disease-free and raised on premium nutrition standards. Perfect for commercial processing and retail meat supply.</p>
                <ul class="check-list" style="margin:1.6rem 0 2.2rem;">
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Ross 308 &amp; Cobb 500 breeds</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Market weight in 6–7 weeks</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Excellent feed conversion ratio</li>
                </ul>
                <a href="/Frontend/pages/shop.php?category=broilers" class="btn btn-primary" data-magnetic>Shop Broilers</a>
            </div>
            <div data-reveal="right">
                <div class="img-frame frame-gold">
                    <img src="/Frontend/images/download (4).png" alt="Broiler chickens" loading="lazy" style="aspect-ratio:4/3;" data-parallax="0.05">
                </div>
            </div>
        </div>
    </div>
</section>

<hr class="ornament-line">

<!-- Layers & Eggs -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4.5rem);align-items:center;">
            <div data-reveal="left" style="order:2;">
                <div class="img-frame frame-gold">
                    <img src="/Frontend/images/download (3).png" alt="Fresh farm eggs" loading="lazy" style="aspect-ratio:4/3;" data-parallax="0.05">
                </div>
            </div>
            <div data-reveal="right" style="order:1;">
                <span class="eyebrow">Egg Production</span>
                <h2 class="section-title">Layers &amp; Fresh <em>Eggs</em></h2>
                <p class="lead">High-yielding layer chickens producing premium quality eggs. Optimized for consistent production and excellent shell quality — harvested daily from our modern facilities.</p>
                <ul class="check-list" style="margin:1.6rem 0 2.2rem;">
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>ISA Brown &amp; Lohmann layers</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>300+ eggs per bird per year</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Grade A farm-fresh eggs (30-egg trays)</li>
                </ul>
                <a href="/Frontend/pages/shop.php?category=layers" class="btn btn-primary" data-magnetic>Shop Layers &amp; Eggs</a>
            </div>
        </div>
    </div>
</section>

<hr class="ornament-line">

<!-- Feeds -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4.5rem);align-items:center;">
            <div data-reveal="left">
                <span class="eyebrow">Nutrition</span>
                <h2 class="section-title">Premium Animal <em>Feeds</em></h2>
                <p class="lead">Specially formulated animal feeds designed for optimal growth, productivity and health — balanced with essential nutrients, amino acids and vitamins for maximum performance.</p>
                <ul class="check-list" style="margin:1.6rem 0 2.2rem;">
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Starter, grower and finisher feeds</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Premium layer mash with calcium</li>
                    <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Available in 50kg bulk bags</li>
                </ul>
                <a href="/Frontend/pages/shop.php?category=feeds" class="btn btn-primary" data-magnetic>Shop Feeds</a>
            </div>
            <div data-reveal="right">
                <div class="img-frame frame-gold">
                    <img src="/Frontend/images/download (8).png" alt="Premium animal feeds" loading="lazy" style="aspect-ratio:4/3;" data-parallax="0.05">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bulk CTA -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="cta-band" data-reveal="zoom">
            <div class="container" style="padding:0;">
                <span class="eyebrow centered" style="color:var(--gold-300);">Bulk Orders</span>
                <h2>Commercial farming needs?</h2>
                <p>We offer specialized pricing and dedicated support for large-scale operations. Bulk orders for day-old chicks and feeds include free delivery within Busia County.</p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <a href="/Frontend/pages/contact.php" class="btn btn-primary" data-magnetic>Request Bulk Quote</a>
                    <a href="/Frontend/pages/shop.php" class="btn btn-ghost">Browse the Shop</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
