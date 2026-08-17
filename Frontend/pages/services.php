<?php
/**
 * Services Page — Premium Redesign
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Our Services - Consulting & Incubator Rentals | Busia Chicken Farm';

include '../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('/Frontend/images/adbg.png');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>Services</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100">Our <em>Services</em></h1>
        <p data-reveal="fade" data-reveal-delay="200">Supporting poultry farmers with advanced expertise, consulting and machinery — so your flock thrives.</p>
    </div>
</section>

<!-- Services Grid -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Professional Support</span>
            <h2 class="section-title">Farming solutions &amp; <em>services</em></h2>
            <p class="lead">We go beyond chick sales to ensure local farmers succeed with comprehensive support systems.</p>
        </div>

        <div class="grid-3" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.8rem;">
            <div class="p-card">
                <div class="p-icon"><i data-lucide="help-circle" style="width:28px;height:28px;"></i></div>
                <h3>Poultry Business Consulting</h3>
                <p>Direct guidance from experienced agronomists — house construction, stocking capacity, lighting, vaccination regimens and ventilation optimization to minimize mortality rates.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="layers" style="width:28px;height:28px;"></i></div>
                <h3>Incubator Rental &amp; Hatching</h3>
                <p>Rent time in our high-capacity commercial egg incubators. Bring your fertile eggs — our calibrated machines manage humidity and temperature for maximum hatch rates.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="book-open" style="width:28px;height:28px;"></i></div>
                <h3>Feed Formulation Seminars</h3>
                <p>Learn feed formulas using locally available ingredients like maize, soya and fish meal. Save up to 40% on operational costs by preparing high-efficiency feeds yourself.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="egg" style="width:28px;height:28px;"></i></div>
                <h3>Day-Old Chick Supply</h3>
                <p>Vaccinated, disease-free day-old chicks of the best breeds, delivered safely to your farm with a documented health and vaccination history.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="school" style="width:28px;height:28px;"></i></div>
                <h3>Training &amp; Farm Visits</h3>
                <p>Hands-on training sessions on housing, feeding, record-keeping and disease prevention — on your farm or ours.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="monitor-smartphone" style="width:28px;height:28px;"></i></div>
                <h3>Farm Management Tools</h3>
                <p>Digital tools for flock records, egg production tracking, feed inventory and sales — plus full management software support.</p>
            </div>
        </div>
    </div>
</section>

<!-- Process Strip -->
<section class="section-pad stats-band">
    <div class="container">
        <div class="grid-4" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.6rem;">
            <div class="stat-item" data-reveal>
                <b><span data-counter="95" data-suffix="%+">0</span></b>
                <span>Hatch Success Rate</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="120">
                <b><span data-counter="40" data-suffix="%">0</span></b>
                <span>Saved on Feed Costs</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="240">
                <b><span data-counter="2" data-suffix="k+">0</span></b>
                <span>Chicks Hatched Monthly</span>
            </div>
            <div class="stat-item" data-reveal data-reveal-delay="360">
                <b><span data-counter="24" data-suffix="/7">0</span></b>
                <span>Support Availability</span>
            </div>
        </div>
    </div>
</section>

<!-- How We Help -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">How We Help</span>
            <h2 class="section-title">Every farmer, <em>fully supported</em></h2>
        </div>
        <div class="grid-4" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1.8rem;">
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

        <div class="cta-band" style="margin-top:clamp(2.4rem,5vw,4rem);" data-reveal="zoom">
            <div class="container" style="padding:0;">
                <h2>Need custom support or consulting?</h2>
                <p>Send us an inquiry or visit our offices in Nasira AC sub-location for in-person support.</p>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <a href="/Frontend/pages/contact.php" class="btn btn-primary" data-magnetic>Book Consultation</a>
                    <a href="tel:+254727585599" class="btn btn-ghost">Call +254 727 585 599</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
