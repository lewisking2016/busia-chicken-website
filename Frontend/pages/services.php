<?php
/**
 * Our Services — Busia Chicken Limited
 * The Chicken People
 * Exact transcript alignment: 6 Core Operations, Statistics, and 4-Step Support Journey
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Our Services - Comprehensive Poultry & Agricultural Solutions | Busia Chicken Limited';

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('/Frontend/images/adbg.webp');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>Services</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100">Our <em>Services</em></h1>
        <p data-reveal="fade" data-reveal-delay="200">Comprehensive Poultry Farming &amp; Sustainable Agricultural Solutions for East Africa.</p>
    </div>
</section>

<!-- Introduction & Overview (Transcript) -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">OUR SERVICES</span>
            <h2 class="section-title">Comprehensive Poultry Farming &amp; <em>Agricultural Solutions</em></h2>
            <p class="lead" style="max-width:880px;margin:0 auto;">At Busia Chicken Limited, we are committed to delivering quality poultry products and sustainable agricultural solutions. Our operations cover egg production, poultry farming, animal feeds manufacturing, organic manure production, organic foliar fertilizers, chicken meat production and poultry farming consultancy. We combine modern farming practices, innovation and expertise to support farmers, businesses and consumers.</p>
        </div>

        <!-- 6 Core Services Grid -->
        <div class="grid-3" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:2rem;margin-top:2.5rem;">
            <!-- 1. Egg Production -->
            <div class="p-card" style="display:flex;flex-direction:column;">
                <div class="p-icon"><i data-lucide="egg" style="width:28px;height:28px;"></i></div>
                <span style="font-weight:800;color:var(--brand-red);font-size:0.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">01. Core Operation</span>
                <h3 style="margin-bottom:0.8rem;">Egg Production</h3>
                <p style="flex-grow:1;color:#475569;line-height:1.65;">We specialize in the production and supply of high-quality, farm-fresh eggs. Through proper flock management, quality nutrition and strict biosecurity measures, we ensure consistent production of nutritious eggs that meet the needs of households, retailers, hotels and other businesses.</p>
                <div style="margin-top:1.2rem;padding-top:1rem;border-top:1px solid #f1f5f9;">
                    <a href="/Frontend/pages/shop.php?category=eggs" class="btn btn-red btn-sm" style="width:100%;justify-content:center;"><i data-lucide="shopping-cart" style="width:14px;height:14px;"></i> Order Fresh Eggs</a>
                </div>
            </div>

            <!-- 2. Organic Manure Production -->
            <div class="p-card" style="display:flex;flex-direction:column;">
                <div class="p-icon"><i data-lucide="leaf" style="width:28px;height:28px;"></i></div>
                <span style="font-weight:800;color:var(--brand-600);font-size:0.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">02. Sustainable Soils</span>
                <h3 style="margin-bottom:0.8rem;">Organic Manure Production</h3>
                <p style="flex-grow:1;color:#475569;line-height:1.65;">We produce organic manure from poultry waste, transforming bird droppings and other poultry remains into valuable organic fertilizer. Our manure improves soil fertility, enhances soil structure and supports sustainable crop production while promoting environmentally responsible waste management.</p>
                <div style="margin-top:1.2rem;padding-top:1rem;border-top:1px solid #f1f5f9;">
                    <a href="/Frontend/pages/contact.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Inquire Manure Supply</a>
                </div>
            </div>

            <!-- 3. Organic Folia Production -->
            <div class="p-card" style="display:flex;flex-direction:column;">
                <div class="p-icon"><i data-lucide="sprout" style="width:28px;height:28px;"></i></div>
                <span style="font-weight:800;color:var(--brand-600);font-size:0.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">03. Crop Productivity</span>
                <h3 style="margin-bottom:0.8rem;">Organic Folia Production</h3>
                <p style="flex-grow:1;color:#475569;line-height:1.65;">We produce Organic Folia, an organic agricultural product designed to support plant growth and improve crop productivity. Our focus is on providing sustainable agricultural solutions that help farmers improve crop performance and promote environmentally friendly farming practices.</p>
                <div style="margin-top:1.2rem;padding-top:1rem;border-top:1px solid #f1f5f9;">
                    <a href="/Frontend/pages/contact.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Inquire Organic Folia</a>
                </div>
            </div>

            <!-- 4. Animal Feeds Production -->
            <div class="p-card" style="display:flex;flex-direction:column;">
                <div class="p-icon"><i data-lucide="wheat" style="width:28px;height:28px;"></i></div>
                <span style="font-weight:800;color:var(--brand-600);font-size:0.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">04. Nutrition Standards</span>
                <h3 style="margin-bottom:0.8rem;">Animal Feeds Production</h3>
                <p style="flex-grow:1;color:#475569;line-height:1.65;">We manufacture quality poultry feeds formulated to support healthy growth, improved productivity and optimal bird performance. Our feeds cater to different poultry growth stages, providing essential nutrients for broilers, layers and other poultry breeds while promoting efficient and affordable poultry farming.</p>
                <div style="margin-top:1.2rem;padding-top:1rem;border-top:1px solid #f1f5f9;">
                    <a href="/Frontend/pages/shop.php?category=feeds" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Explore Feeds</a>
                </div>
            </div>

            <!-- 5. Chicken Farming Consultancy -->
            <div class="p-card" style="display:flex;flex-direction:column;">
                <div class="p-icon"><i data-lucide="graduation-cap" style="width:28px;height:28px;"></i></div>
                <span style="font-weight:800;color:var(--brand-600);font-size:0.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">05. Expert Advisory</span>
                <h3 style="margin-bottom:0.8rem;">Chicken Farming Consultancy</h3>
                <p style="flex-grow:1;color:#475569;line-height:1.65;">We provide professional poultry farming consultancy to individuals, farmers and agricultural businesses looking to establish, manage or expand their poultry farming operations. Our consultancy covers farm planning, housing, breed selection, feeding programs, disease prevention, flock management, biosecurity and production optimization.</p>
                <div style="margin-top:1.2rem;padding-top:1rem;border-top:1px solid #f1f5f9;">
                    <a href="/Frontend/pages/contact.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Book Advisory Session</a>
                </div>
            </div>

            <!-- 6. Chicken Meat Production -->
            <div class="p-card" style="display:flex;flex-direction:column;">
                <div class="p-icon"><i data-lucide="shield-check" style="width:28px;height:28px;"></i></div>
                <span style="font-weight:800;color:var(--brand-red);font-size:0.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">06. Premium Table Meat</span>
                <h3 style="margin-bottom:0.8rem;">Chicken Meat Production</h3>
                <p style="flex-grow:1;color:#475569;line-height:1.65;">We engage in chicken meat production, supplying quality poultry meat to households, hotels, restaurants, retailers and other consumers. Through proper bird management, quality feeding and responsible farming practices, we aim to provide healthy, fresh and quality chicken products.</p>
                <div style="margin-top:1.2rem;padding-top:1rem;border-top:1px solid #f1f5f9;">
                    <a href="/Frontend/pages/shop.php?category=chicken" class="btn btn-red btn-sm" style="width:100%;justify-content:center;"><i data-lucide="shopping-cart" style="width:14px;height:14px;"></i> Shop Chicken Meat</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Band (Exact Transcript) -->
<section class="section-pad stats-band">
    <div class="container">
        <div class="grid-3" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:2rem;text-align:center;">
            <div class="stat-item" data-reveal>
                <b><span data-counter="90" data-suffix="%">0</span></b>
                <span>Hatch Success Rate</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="120">
                <b><span data-counter="30" data-suffix="%">0</span></b>
                <span>Saved on Feed Costs</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="240">
                <b><span data-counter="100" data-suffix="%">0</span></b>
                <span>Support Availability</span>
            </div>
        </div>
    </div>
</section>

<!-- How We Help (Exact Transcript) -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered" style="color:var(--brand-red);font-weight:800;letter-spacing:0.08em;"><i data-lucide="check-circle" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;"></i> HOW WE EMPOWER FARMERS</span>
            <h2 class="section-title">Every farmer, <em>fully supported</em></h2>
            <p class="lead">A proven 4-stage process to ensure your agricultural venture flourishes.</p>
        </div>

        <div class="grid-4" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.8rem;">
            <div class="p-card num-card">
                <span class="step-no">01</span>
                <h3>Book a Consultation</h3>
                <p>Reach us by phone, WhatsApp or the contact form. We respond within 24 hours.</p>
            </div>
            <div class="p-card num-card">
                <span class="step-no">02</span>
                <h3>Site &amp; Stock Assessment</h3>
                <p>We visit or review your setup and recommend the right birds, feeds and housing.</p>
            </div>
            <div class="p-card num-card">
                <span class="step-no">03</span>
                <h3>Ongoing Support</h3>
                <p>Vaccination schedules, feed adjustments and problem-solving whenever you need it.</p>
            </div>
            <div class="p-card num-card">
                <span class="step-no">04</span>
                <h3>Grow &amp; Scale</h3>
                <p>Expand confidently with our hatchery, equipment and market guidance.</p>
            </div>
        </div>
    </div>
</section>

<!-- In-Person Support & CTA (Transcript) -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="cta-band" data-reveal="zoom">
            <div class="container" style="padding:0;">
                <span class="eyebrow centered" style="color:var(--gold-300);">In-Person &amp; Custom Support</span>
                <h2>Need custom support or consulting?</h2>
                <p>Send us an inquiry or visit our offices in Nasira AC sub-location for in-person support.</p>
                <div style="display:flex;gap:1.2rem;flex-wrap:wrap;justify-content:center;margin-top:1.5rem;">
                    <a href="/Frontend/pages/contact.php" class="btn btn-primary" data-magnetic>Book Consultation</a>
                    <a href="tel:+254727585599" class="btn btn-ghost" style="display:inline-flex;align-items:center;gap:8px;">
                        <i data-lucide="phone"></i> Call: +254 727 585 599
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>
