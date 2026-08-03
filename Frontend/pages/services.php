<?php
/**
 * Services Page
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Our Services - Consulting & Incubator Rentals | Busia Chicken Farm';

include '../includes/header.php';
?>

<!-- Services Hero -->
<section style="padding: var(--space-4xl) 0 var(--space-2xl); background-image: url('/Frontend/images/adbg.png'); background-size: cover; background-position: center; background-repeat: no-repeat; border-bottom: 1px solid rgba(255,255,255,0.12); position: relative;">
    <div style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.35);"></div>
    <div class="container" style="position: relative; z-index: 1; text-align: center; color: #FFFFFF;">
        <h1 style="margin-bottom: var(--space-sm); color: #FFFFFF;">Our Services</h1>
        <p style="font-size: 1.125rem; color: rgba(255,255,255,0.9);">Supporting poultry farmers with advanced expertise, consulting, and machinery.</p>
    </div>
</section>

<!-- Main Services Grid -->
<section style="padding: var(--space-4xl) 0; background: #ffffff;">
    <div class="container">
        
        <div style="text-align: center; max-width: 600px; margin: 0 auto 50px;">
            <span style="display: inline-block; padding: 4px 12px; background: rgba(27,94,32,0.08); color: var(--primary); font-weight: 700; border-radius: var(--radius-pill); font-size: 0.75rem; text-transform: uppercase; margin-bottom: 15px;">Professional Support</span>
            <h2>Farming Solutions & Services</h2>
            <p style="color: var(--gray-600); margin-top: 10px;">We go beyond chick sales to ensure local farmers succeed with comprehensive support systems.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            
            <!-- Service 1: Poultry Consulting -->
            <div class="admin-card" style="padding: var(--space-xl); transition: transform 0.3s; border: 1px solid rgba(0,0,0,0.06); border-radius: var(--radius-lg);">
                <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: rgba(27,94,32,0.08); color: var(--primary); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
                </div>
                <h4 style="margin-bottom: 12px; font-weight: 700;">Poultry Business Consulting</h4>
                <p style="color: var(--gray-600); font-size: 0.92rem; line-height: 1.6; margin-bottom: 20px;">
                    Get direct guidance from experienced agronomists. We advise on house construction, stocking capacity, lighting, vaccination regimens, and ventilation optimization to minimize mortality rates.
                </p>
            </div>

            <!-- Service 2: Incubator & Hatchery Hire -->
            <div class="admin-card" style="padding: var(--space-xl); transition: transform 0.3s; border: 1px solid rgba(0,0,0,0.06); border-radius: var(--radius-lg);">
                <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: rgba(27,94,32,0.08); color: var(--primary); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
                </div>
                <h4 style="margin-bottom: 12px; font-weight: 700;">Incubator Rental & Hatching</h4>
                <p style="color: var(--gray-600); font-size: 0.92rem; line-height: 1.6; margin-bottom: 20px;">
                    Rent time in our high-capacity commercial egg incubators. Bring your fertile eggs, and let our calibrated machines manage humidity and temperature controls for maximum hatch rates.
                </p>
            </div>

            <!-- Service 3: Training & Feed Formulation -->
            <div class="admin-card" style="padding: var(--space-xl); transition: transform 0.3s; border: 1px solid rgba(0,0,0,0.06); border-radius: var(--radius-lg);">
                <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: rgba(27,94,32,0.08); color: var(--primary); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i data-lucide="book-open" style="width: 24px; height: 24px;"></i>
                </div>
                <h4 style="margin-bottom: 12px; font-weight: 700;">Feed Formulation Seminars</h4>
                <p style="color: var(--gray-600); font-size: 0.92rem; line-height: 1.6; margin-bottom: 20px;">
                    Learn feed formulas using locally available ingredients like maize, soya, fish meal, and premixes. Save up to 40% on operational costs by preparing high-efficiency feeds yourself.
                </p>
            </div>

        </div>

        <!-- Call to Action -->
        <div style="margin-top: 60px; background: #f8fafc; padding: 40px; border-radius: var(--radius-lg); text-align: center; border: 1px solid rgba(0,0,0,0.03);">
            <h4 style="font-weight: 700; margin-bottom: 10px;">Need Custom Support or Consulting?</h4>
            <p style="color: var(--gray-600); max-width: 500px; margin: 0 auto 24px; font-size: 0.95rem;">Send us an inquiry or visit our offices in Nasira AC sub-location for in-person support.</p>
            <a href="/Frontend/pages/contact.php" class="btn btn-primary">Book Consultation</a>
        </div>

    </div>
</section>

<?php
include '../includes/footer.php';
?>
