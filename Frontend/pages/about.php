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
<section class="page-hero" style="background-image:url('/Frontend/images/download (2).png');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>About</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100">About <em>Busia Chicken Farm</em></h1>
        <p data-reveal="fade" data-reveal-delay="200">Leading poultry supplier in East Africa — premium quality chickens, eggs and animal feeds since 2015.</p>
    </div>
</section>

<!-- Company Story (Exact Transcript) -->
<section class="section-pad bg-white">
    <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4.5rem);align-items:center;">
        <div data-reveal="left">
            <span class="eyebrow">Our Story</span>
            <h2 class="section-title">From a small family operation to <em>industry leaders</em></h2>
            <p class="lead">Founded in 2015, Busia Chicken Farm started as a small family operation in Nasira AC, Busia. What began with just 300 birds has grown into a modern poultry production facility serving thousands of customers across East Africa.</p>
            <p class="lead">Our journey has been driven by a commitment to quality, innovation, and sustainable farming practices. We've invested in state-of-the-art facilities, modern automated equipment, and strict biosafety protocols to ensure every product meets international standards.</p>
            <ul class="check-list" style="margin-top:1.8rem;">
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>100% biosafety compliant standards</li>
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Fully vaccinated, optimal health &amp; early growth</li>
                <li><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>Nutrient-dense organic feed formulas</li>
            </ul>
        </div>
        <div data-reveal="right">
            <div class="img-frame frame-gold">
                <img src="/Frontend/images/download (5).png" alt="Busia Chicken Farm automated egg sorting and grading facility" loading="lazy" style="aspect-ratio:4/3;" data-parallax="0.05">
            </div>
        </div>
    </div>
</section>

<!-- Stats Band -->
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

<!-- Core Values (Exact Transcript) -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered" style="color:var(--brand-red);font-weight:800;letter-spacing:0.08em;"><i data-lucide="shield" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;"></i> OUR GUIDING PRINCIPLES</span>
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

<!-- Certifications (Exact Transcript) -->
<section class="section-pad" style="background:var(--cream-50);">
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
                <div class="p-icon" style="margin:0 auto 1.2rem;"><i data-lucide="shield-check" style="width:28px;height:28px;"></i></div>
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

<!-- Two Operating Locations (Google Business) -->
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Our Footprint</span>
            <h2 class="section-title">Two Strategically Located <em>Hubs</em></h2>
            <p class="lead">From farm production in Nasira to direct customer access at Tangakona.</p>
        </div>

        <div class="locations-grid" data-reveal-group>
            <div class="location-card">
                <span class="location-badge"><i data-lucide="building"></i> Primary Farm &amp; Hatchery</span>
                <h3 class="location-title">Nasira AC Sub-location</h3>
                <p class="location-desc">Our primary production facility spanning modern brooding units, clinical egg incubation and layer management houses. Strict biosecurity prevents any outside disease exposure.</p>
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
                <span class="location-badge hub"><i data-lucide="store"></i> Feed Depot &amp; Branch</span>
                <h3 class="location-title">Tangakona Branch</h3>
                <p class="location-desc">Our high-throughput sales hub along the Busia-Kisumu Highway. Farmers collect fresh feeds, day-old chicks and 30-egg trays without having to navigate deep rural access roads.</p>
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
