<?php
/**
 * Products Page
 * Premium Minimalist Redesign
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Products - Chicken & Feeds | Busia Chicken Farm';

include '../includes/header.php';
?>

<!-- Clean Hero Section -->
<section style="padding: var(--space-4xl) 0; position: relative; overflow: hidden; min-height: 50vh; display: flex; align-items: center;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;">
        <img src="/Frontend/images/adbg.png" alt="Our Premium Products" style="width: 100%; height: 100%; object-fit: cover;">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.7), rgba(0,0,0,0.4));"></div>
    </div>
    <div class="container" style="max-width: 800px; text-align: center; position: relative; z-index: 2; color: white;">
        <h1 style="margin-bottom: var(--space-lg); color: white;">Our Premium Products</h1>
        <p style="font-size: 1.25rem; color: rgba(255,255,255,0.9); margin-bottom: 0;">
            Discover our range of high-quality chicken products and specially formulated animal feeds designed for optimal growth and productivity.
        </p>
    </div>
</section>

<!-- Product Overview -->
<section style="padding: var(--space-4xl) 0; background-color: var(--white);">
    <div class="container">
        
        <!-- Section 1: Broilers -->
        <div class="grid-2" style="margin-bottom: var(--space-4xl);">
            <div>
                <div style="display: inline-block; padding: 0.5rem 1rem; background: var(--gray-100); color: var(--primary); font-weight: 600; font-size: 0.875rem; border-radius: var(--radius-pill); margin-bottom: var(--space-lg);">
                    Meat Production
                </div>
                <h2 style="margin-bottom: var(--space-lg);">Broiler Chickens</h2>
                <p style="color: var(--gray-600); font-size: 1.125rem; margin-bottom: var(--space-lg);">
                    High-quality broilers bred for superior meat yield and rapid growth cycles. Our broilers are vaccinated, disease-free, and raised on premium nutrition standards. Perfect for commercial processing and retail meat supply.
                </p>
                <ul style="list-style: none; margin-bottom: var(--space-xl);">
                    <li style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-sm); color: var(--dark); font-weight: 500;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Ross 308 & Cobb 500 Breeds
                    </li>
                    <li style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-sm); color: var(--dark); font-weight: 500;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Market weight in 6-7 weeks
                    </li>
                    <li style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-sm); color: var(--dark); font-weight: 500;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Excellent feed conversion ratio
                    </li>
                </ul>
                <a href="/Frontend/pages/shop.php?category=broilers" class="btn btn-primary">Shop Broilers</a>
            </div>
            <div style="position: relative;">
                <img src="/Frontend/images/download (4).png" alt="Broiler Chickens" style="width: 100%; border-radius: var(--radius-sm); display: block;">
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--gray-200); margin: var(--space-4xl) 0;">

        <!-- Section 2: Layers & Eggs -->
        <div class="grid-2" style="margin-bottom: var(--space-4xl);">
            <div style="position: relative; order: 2;">
                <img src="/Frontend/images/download (3).png" alt="Fresh Eggs" style="width: 100%; border-radius: var(--radius-sm); display: block;">
            </div>
            <div style="order: 1;">
                <div style="display: inline-block; padding: 0.5rem 1rem; background: var(--gray-100); color: var(--primary); font-weight: 600; font-size: 0.875rem; border-radius: var(--radius-pill); margin-bottom: var(--space-lg);">
                    Egg Production
                </div>
                <h2 style="margin-bottom: var(--space-lg);">Layers & Fresh Eggs</h2>
                <p style="color: var(--gray-600); font-size: 1.125rem; margin-bottom: var(--space-lg);">
                    High-yielding layer chickens producing premium quality eggs. Our layers are optimized for consistent production and excellent shell quality. Fresh eggs are harvested daily from our modern facilities.
                </p>
                <ul style="list-style: none; margin-bottom: var(--space-xl);">
                    <li style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-sm); color: var(--dark); font-weight: 500;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        ISA Brown & Lohmann Layers
                    </li>
                    <li style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-sm); color: var(--dark); font-weight: 500;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        300+ eggs per year per bird
                    </li>
                    <li style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-sm); color: var(--dark); font-weight: 500;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Grade A farm-fresh eggs (30-egg trays)
                    </li>
                </ul>
                <a href="/Frontend/pages/shop.php?category=layers" class="btn btn-primary">Shop Layers & Eggs</a>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--gray-200); margin: var(--space-4xl) 0;">

        <!-- Section 3: Animal Feeds -->
        <div class="grid-2">
            <div>
                <div style="display: inline-block; padding: 0.5rem 1rem; background: var(--gray-100); color: var(--primary); font-weight: 600; font-size: 0.875rem; border-radius: var(--radius-pill); margin-bottom: var(--space-lg);">
                    Nutrition
                </div>
                <h2 style="margin-bottom: var(--space-lg);">Premium Animal Feeds</h2>
                <p style="color: var(--gray-600); font-size: 1.125rem; margin-bottom: var(--space-lg);">
                    Specially formulated animal feeds designed for optimal growth, productivity, and health. Each formula is balanced with essential nutrients, amino acids, and vitamins for maximum performance.
                </p>
                <ul style="list-style: none; margin-bottom: var(--space-xl);">
                    <li style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-sm); color: var(--dark); font-weight: 500;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Starter, Grower, and Finisher feeds
                    </li>
                    <li style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-sm); color: var(--dark); font-weight: 500;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Premium Layer Mash with calcium
                    </li>
                    <li style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-sm); color: var(--dark); font-weight: 500;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Available in 50kg bulk bags
                    </li>
                </ul>
                <a href="/Frontend/pages/shop.php?category=feeds" class="btn btn-primary">Shop Feeds</a>
            </div>
            <div style="position: relative;">
                <img src="/Frontend/images/download (8).png" alt="Animal Feeds" style="width: 100%; border-radius: var(--radius-sm); display: block;">
            </div>
        </div>

    </div>
</section>

<!-- Bulk Orders CTA -->
<section style="padding: var(--space-4xl) 0; background-color: var(--dark); color: var(--white); text-align: center;">
    <div class="container" style="max-width: 700px;">
        <h2 style="color: var(--white); margin-bottom: var(--space-md);">Commercial Farming Needs?</h2>
        <p style="font-size: 1.125rem; color: var(--gray-400); margin-bottom: var(--space-2xl);">
            We offer specialized pricing and dedicated support for large-scale operations. Bulk orders for day-old chicks and feeds include free delivery within Busia County.
        </p>
        <a href="/Frontend/pages/contact.php" class="btn btn-accent">Request Bulk Quote</a>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
