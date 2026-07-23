<?php
/**
 * About Us Page
 * Premium Minimalist Redesign
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'About Us - Busia Chicken Farm';

include '../includes/header.php';
?>

<!-- Clean Hero Section -->
<section style="padding: var(--space-4xl) 0; position: relative; overflow: hidden; min-height: 50vh; display: flex; align-items: center;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;">
        <img src="/Frontend/images/download (2).png" alt="About Busia Chicken" style="width: 100%; height: 100%; object-fit: cover;">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.7), rgba(0,0,0,0.5));"></div>
    </div>
    <div class="container" style="max-width: 800px; text-align: center; position: relative; z-index: 2; color: white;">
        <h1 style="margin-bottom: var(--space-lg); color: white;">About Busia Chicken Farm</h1>
        <p style="font-size: 1.25rem; color: rgba(255,255,255,0.9); margin-bottom: 0;">
            Leading poultry supplier in East Africa. Premium quality chickens, eggs, and animal feeds since 2015.
        </p>
    </div>
</section>

<!-- Company Story -->
<section style="padding: var(--space-4xl) 0; background-color: var(--white);">
    <div class="container grid-2" style="align-items: center;">
        <div>
            <div style="display: inline-block; padding: 0.5rem 1rem; background: var(--gray-100); color: var(--primary); font-weight: 600; font-size: 0.875rem; border-radius: var(--radius-pill); margin-bottom: var(--space-lg);">
                Our Story
            </div>
            <h2 style="margin-bottom: var(--space-xl);">From a small family operation to industry leaders.</h2>
            <p style="color: var(--gray-600); font-size: 1.125rem; margin-bottom: var(--space-md);">
                Founded in 2015, Busia Chicken Farm started as a small family operation in Nasira AC, Busia. What began with just 500 birds has grown into a modern poultry production facility serving thousands of customers across East Africa.
            </p>
            <p style="color: var(--gray-600); font-size: 1.125rem; margin-bottom: var(--space-md);">
                Our journey has been driven by a commitment to quality, innovation, and sustainable farming practices. We've invested in state-of-the-art facilities, modern incubation equipment, and strict biosafety protocols to ensure every product meets international standards.
            </p>
        </div>
        <div style="background: var(--gray-50); padding: var(--space-2xl); border-radius: var(--radius-sm); border: 1px solid var(--gray-200); display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="text-align: center; padding: 16px; background: white; border-radius: 4px; border: 1px solid var(--gray-100);">
                <i data-lucide="award" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 12px;"></i>
                <h4 style="margin: 0 0 6px 0;">Quality Certified</h4>
                <p style="font-size: 0.85rem; color: var(--gray-600); margin: 0;">100% biosafety compliant standards.</p>
            </div>
            <div style="text-align: center; padding: 16px; background: white; border-radius: 4px; border: 1px solid var(--gray-100);">
                <i data-lucide="shield-check" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 12px;"></i>
                <h4 style="margin: 0 0 6px 0;">Fully Vaccinated</h4>
                <p style="font-size: 0.85rem; color: var(--gray-600); margin: 0;">Optimal health & early growth.</p>
            </div>
            <div style="text-align: center; padding: 16px; background: white; border-radius: 4px; border: 1px solid var(--gray-100);">
                <i data-lucide="sprout" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 12px;"></i>
                <h4 style="margin: 0 0 6px 0;">Organic Feed</h4>
                <p style="font-size: 0.85rem; color: var(--gray-600); margin: 0;">Nutrient-dense custom formulas.</p>
            </div>
            <div style="text-align: center; padding: 16px; background: white; border-radius: 4px; border: 1px solid var(--gray-100);">
                <i data-lucide="truck" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 12px;"></i>
                <h4 style="margin: 0 0 6px 0;">Safe Transit</h4>
                <p style="font-size: 0.85rem; color: var(--gray-600); margin: 0;">Carefully monitored deliveries.</p>
            </div>
        </div>
    </div>
</section>

<!-- Key Statistics -->
<section style="padding: var(--space-4xl) 0; background-color: var(--dark); color: var(--white);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-3xl); text-align: center;">
            <div>
                <div style="font-size: 3.5rem; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--accent); margin-bottom: var(--space-xs);">
                    <span class="stat-counter" data-target="10" data-suffix="k+">0</span>
                </div>
                <div style="font-size: 1rem; color: var(--gray-400); font-weight: 500;">Chickens Raised Annually</div>
            </div>
            <div>
                <div style="font-size: 3.5rem; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--accent); margin-bottom: var(--space-xs);">
                    <span class="stat-counter" data-target="5" data-suffix="k+">0</span>
                </div>
                <div style="font-size: 1rem; color: var(--gray-400); font-weight: 500;">Satisfied Customers</div>
            </div>
            <div>
                <div style="font-size: 3.5rem; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--accent); margin-bottom: var(--space-xs);">
                    <span class="stat-counter" data-target="10" data-suffix="+">0</span>
                </div>
                <div style="font-size: 1rem; color: var(--gray-400); font-weight: 500;">Years in Business</div>
            </div>
            <div>
                <div style="font-size: 3.5rem; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--accent); margin-bottom: var(--space-xs);">
                    <span class="stat-counter" data-target="100" data-suffix="%">0</span>
                </div>
                <div style="font-size: 1rem; color: var(--gray-400); font-weight: 500;">Quality Guarantee</div>
            </div>
        </div>
    </div>
</section>

<!-- Our Values -->
<section style="padding: var(--space-4xl) 0; background-color: var(--gray-50);">
    <div class="container">
        <div class="section-header">
            <h2>Our Core Values</h2>
            <p>The principles that guide our daily operations and long-term vision.</p>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-2xl);">
            <div style="background: var(--white); padding: var(--space-2xl); border-radius: var(--radius-sm); border: 1px solid var(--gray-200);">
                <div style="width: 48px; height: 48px; background: var(--gray-100); border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-lg); color: var(--primary);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3 style="margin-bottom: var(--space-md); font-size: 1.5rem;">Quality First</h3>
                <p style="color: var(--gray-600); margin: 0;">Every product meets strict quality standards. We prioritize health, genetics, and product excellence above all else.</p>
            </div>
            <div style="background: var(--white); padding: var(--space-2xl); border-radius: var(--radius-sm); border: 1px solid var(--gray-200);">
                <div style="width: 48px; height: 48px; background: var(--gray-100); border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-lg); color: var(--primary);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <h3 style="margin-bottom: var(--space-md); font-size: 1.5rem;">Sustainability</h3>
                <p style="color: var(--gray-600); margin: 0;">We practice responsible farming with proper waste management, animal welfare, and environmental stewardship.</p>
            </div>
            <div style="background: var(--white); padding: var(--space-2xl); border-radius: var(--radius-sm); border: 1px solid var(--gray-200);">
                <div style="width: 48px; height: 48px; background: var(--gray-100); border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-lg); color: var(--primary);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <h3 style="margin-bottom: var(--space-md); font-size: 1.5rem;">Customer Focus</h3>
                <p style="color: var(--gray-600); margin: 0;">We're committed to excellent service, transparent communication, and building lasting relationships with our customers.</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section style="padding: var(--space-4xl) 0; background-color: var(--white);">
    <div class="container">
        <div class="section-header">
            <h2>Leadership Team</h2>
            <p>The dedicated professionals driving our mission forward.</p>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-3xl);">
            <div style="text-align: center;">
                <div style="width: 100px; height: 100px; margin: 0 auto var(--space-lg); border-radius: 4px; display: flex; align-items: center; justify-content: center; background: rgba(27, 94, 32, 0.05); color: var(--primary);">
                    <i data-lucide="shield" style="width: 48px; height: 48px;"></i>
                </div>
                <h3 style="margin-bottom: var(--space-xs); font-size: 1.5rem;">Samuel Kiplagat</h3>
                <p style="color: var(--primary); font-weight: 600; margin-bottom: var(--space-sm);">Farm Director</p>
                <p style="color: var(--gray-600); font-size: 0.95rem;">20+ years poultry farming experience. Oversees all farm operations and biosafety protocols.</p>
            </div>
            <div style="text-align: center;">
                <div style="width: 100px; height: 100px; margin: 0 auto var(--space-lg); border-radius: 4px; display: flex; align-items: center; justify-content: center; background: rgba(27, 94, 32, 0.05); color: var(--primary);">
                    <i data-lucide="badge-dollar-sign" style="width: 48px; height: 48px;"></i>
                </div>
                <h3 style="margin-bottom: var(--space-xs); font-size: 1.5rem;">Grace Wanjiru</h3>
                <p style="color: var(--primary); font-weight: 600; margin-bottom: var(--space-sm);">Sales Manager</p>
                <p style="color: var(--gray-600); font-size: 0.95rem;">Customer relations specialist ensuring quality service and timely delivery to all clients.</p>
            </div>
            <div style="text-align: center;">
                <div style="width: 100px; height: 100px; margin: 0 auto var(--space-lg); border-radius: 4px; display: flex; align-items: center; justify-content: center; background: rgba(27, 94, 32, 0.05); color: var(--primary);">
                    <i data-lucide="settings-2" style="width: 48px; height: 48px;"></i>
                </div>
                <h3 style="margin-bottom: var(--space-xs); font-size: 1.5rem;">Peter Omondi</h3>
                <p style="color: var(--primary); font-weight: 600; margin-bottom: var(--space-sm);">Operations Manager</p>
                <p style="color: var(--gray-600); font-size: 0.95rem;">Manages inventory, logistics, and digital farm management systems for efficiency.</p>
            </div>
        </div>
    </div>
</section>

<!-- Certifications -->
<section style="padding: var(--space-4xl) 0; background-color: var(--gray-50); border-top: 1px solid var(--gray-200);">
    <div class="container">
        <div class="section-header">
            <h2>Certifications & Compliance</h2>
            <p>Operating to the highest standards of safety and quality.</p>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-2xl); text-align: center;">
            <div style="padding: var(--space-xl); background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--radius-sm);">
                <div style="margin-bottom: var(--space-md); display: flex; justify-content: center;">
                    <img src="/Frontend/images/busia logo.png" alt="Logo" style="height: 40px; width: auto; opacity: 0.8;">
                </div>
                <p style="font-weight: 700; color: var(--dark); margin-bottom: var(--space-xs);">KEBS Certified</p>
                <p style="font-size: 0.875rem; color: var(--gray-600); margin: 0;">Kenya Bureau of Standards</p>
            </div>
            <div style="padding: var(--space-xl); background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--radius-sm);">
                <div style="margin-bottom: var(--space-md); display: flex; justify-content: center;">
                    <img src="/Frontend/images/busia logo.png" alt="Logo" style="height: 40px; width: auto; opacity: 0.8;">
                </div>
                <p style="font-weight: 700; color: var(--dark); margin-bottom: var(--space-xs);">ISO 22000</p>
                <p style="font-size: 0.875rem; color: var(--gray-600); margin: 0;">Food Safety Management</p>
            </div>
            <div style="padding: var(--space-xl); background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--radius-sm);">
                <div style="margin-bottom: var(--space-md); display: flex; justify-content: center;">
                    <img src="/Frontend/images/busia logo.png" alt="Logo" style="height: 40px; width: auto; opacity: 0.8;">
                </div>
                <p style="font-weight: 700; color: var(--dark); margin-bottom: var(--space-xs);">Biosafety</p>
                <p style="font-size: 0.875rem; color: var(--gray-600); margin: 0;">Ministry of Agriculture</p>
            </div>
            <div style="padding: var(--space-xl); background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--radius-sm);">
                <div style="margin-bottom: var(--space-md); display: flex; justify-content: center;">
                    <img src="/Frontend/images/busia logo.png" alt="Logo" style="height: 40px; width: auto; opacity: 0.8;">
                </div>
                <p style="font-weight: 700; color: var(--dark); margin-bottom: var(--space-xs);">VAT Registered</p>
                <p style="font-size: 0.875rem; color: var(--gray-600); margin: 0;">KRA Compliant</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section style="padding: var(--space-4xl) 0; background-color: var(--white); text-align: center;">
    <div class="container">
        <h2 style="margin-bottom: var(--space-md);">Ready to Partner With Us?</h2>
        <p style="font-size: 1.125rem; color: var(--gray-600); margin-bottom: var(--space-2xl); max-width: 600px; margin-left: auto; margin-right: auto;">
            Whether you're a commercial farm, retailer, or family looking for quality poultry products, we're here to help.
        </p>
        <div style="display: flex; gap: var(--space-md); justify-content: center; flex-wrap: wrap;">
            <a href="contact.php" class="btn btn-primary">Contact Us</a>
            <a href="shop.php" class="btn btn-outline">Shop Now</a>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>