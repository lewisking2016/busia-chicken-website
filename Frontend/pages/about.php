<?php
/**
 * About Us Page — Premium Redesign
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'About Us - Busia Chicken Farm';

include '../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('/Frontend/images/download (2).png');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>About</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100">About <em>Busia Chicken Farm</em></h1>
        <p data-reveal="fade" data-reveal-delay="200">Leading poultry supplier in East Africa — premium quality chickens, eggs and animal feeds since 2015.</p>
    </div>
</section>

<!-- Company Story -->
<section class="section-pad bg-white">
    <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4.5rem);align-items:center;">
        <div data-reveal="left">
            <span class="eyebrow">Our Story</span>
            <h2 class="section-title">From a small family operation to <em>industry leaders</em></h2>
            <p class="lead">Founded in 2015, Busia Chicken Farm started as a small family operation in Nasira AC, Busia. What began with just 500 birds has grown into a modern poultry production facility serving thousands of customers across East Africa.</p>
            <p class="lead">Our journey has been driven by a commitment to quality, innovation, and sustainable farming practices. We've invested in state-of-the-art facilities, modern incubation equipment, and strict biosafety protocols to ensure every product meets international standards.</p>
            <ul class="check-list" style="margin-top:1.8rem;">
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>100% biosafety compliant standards</li>
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Fully vaccinated, optimal health &amp; early growth</li>
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Nutrient-dense organic feed formulas</li>
            </ul>
        </div>
        <div data-reveal="right">
            <div class="img-frame frame-gold">
                <img src="/Frontend/images/download (3).png" alt="Busia Chicken Farm — fresh eggs" loading="lazy" style="aspect-ratio:4/3;" data-parallax="0.05">
            </div>
        </div>
    </div>
</section>

<!-- Stats Band -->
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

<!-- Core Values -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Our Core Values</span>
            <h2 class="section-title">The principles that <em>guide us</em></h2>
        </div>
        <div class="grid-3" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.8rem;">
            <div class="p-card">
                <div class="p-icon"><i data-lucide="badge-check" style="width:28px;height:28px;"></i></div>
                <h3>Quality First</h3>
                <p>Every product meets strict quality standards. We prioritize health, genetics and product excellence above all else.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="leaf" style="width:28px;height:28px;"></i></div>
                <h3>Sustainability</h3>
                <p>Responsible farming with proper waste management, animal welfare and environmental stewardship at the core.</p>
            </div>
            <div class="p-card">
                <div class="p-icon"><i data-lucide="users" style="width:28px;height:28px;"></i></div>
                <h3>Customer Focus</h3>
                <p>Excellent service, transparent communication and lasting relationships with every farmer and family we serve.</p>
            </div>
        </div>
    </div>
</section>

<!-- Team -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Leadership Team</span>
            <h2 class="section-title">The people behind <em>the farm</em></h2>
        </div>
        <div class="grid-3" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.8rem;">
            <div class="p-card" style="text-align:center;">
                <div class="p-icon" style="margin:0 auto 1.2rem;"><i data-lucide="shield" style="width:28px;height:28px;"></i></div>
                <h3>Samuel Kiplagat</h3>
                <p style="color:var(--brand-600);font-weight:700;margin-bottom:0.6rem;">Farm Director</p>
                <p>20+ years poultry farming experience. Oversees all farm operations and biosafety protocols.</p>
            </div>
            <div class="p-card" style="text-align:center;">
                <div class="p-icon" style="margin:0 auto 1.2rem;"><i data-lucide="badge-dollar-sign" style="width:28px;height:28px;"></i></div>
                <h3>Grace Wanjiru</h3>
                <p style="color:var(--brand-600);font-weight:700;margin-bottom:0.6rem;">Sales Manager</p>
                <p>Customer relations specialist ensuring quality service and timely delivery to all clients.</p>
            </div>
            <div class="p-card" style="text-align:center;">
                <div class="p-icon" style="margin:0 auto 1.2rem;"><i data-lucide="settings-2" style="width:28px;height:28px;"></i></div>
                <h3>Peter Omondi</h3>
                <p style="color:var(--brand-600);font-weight:700;margin-bottom:0.6rem;">Operations Manager</p>
                <p>Manages inventory, logistics and digital farm management systems for maximum efficiency.</p>
            </div>
        </div>
    </div>
</section>

<!-- Certifications -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Certifications</span>
            <h2 class="section-title">Operating to the <em>highest standards</em></h2>
        </div>
        <div class="grid-4" data-reveal-group style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.6rem;">
            <div class="p-card" style="text-align:center;">
                <div class="p-icon" style="margin:0 auto 1.2rem;"><i data-lucide="award" style="width:28px;height:28px;"></i></div>
                <h3 style="font-size:1.1rem;">KEBS Certified</h3>
                <p style="font-size:0.9rem;">Kenya Bureau of Standards</p>
            </div>
            <div class="p-card" style="text-align:center;">
                <div class="p-icon" style="margin:0 auto 1.2rem;"><i data-lucide="file-check" style="width:28px;height:28px;"></i></div>
                <h3 style="font-size:1.1rem;">ISO 22000</h3>
                <p style="font-size:0.9rem;">Food Safety Management</p>
            </div>
            <div class="p-card" style="text-align:center;">
                <div class="p-icon" style="margin:0 auto 1.2rem;"><i data-lucide="flask-conical" style="width:28px;height:28px;"></i></div>
                <h3 style="font-size:1.1rem;">Biosafety</h3>
                <p style="font-size:0.9rem;">Ministry of Agriculture</p>
            </div>
            <div class="p-card" style="text-align:center;">
                <div class="p-icon" style="margin:0 auto 1.2rem;"><i data-lucide="receipt" style="width:28px;height:28px;"></i></div>
                <h3 style="font-size:1.1rem;">VAT Registered</h3>
                <p style="font-size:0.9rem;">KRA Compliant</p>
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
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <a href="contact.php" class="btn btn-primary" data-magnetic>Contact Us</a>
                    <a href="shop.php" class="btn btn-ghost">Shop Now</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
