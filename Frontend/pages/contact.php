<?php
/**
 * Contact Us Page — Busia Chicken Limited
 * The Chicken People
 * Featuring both Google Business locations: Nasira AC Sub-location HQ & Tangakona Farm
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Contact & Locations - Nasira HQ & Tangakona Farm | Busia Chicken Limited';

include __DIR__ . '/../includes/header.php';

// Handle form submission
$form_submitted = false;
$form_message = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $phone && $subject && $message) {
        $pdo = function_exists('getDB') ? getDB() : (function_exists('getDatabaseConnection') ? getDatabaseConnection() : null);
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $email, $phone, $subject, $message]);
            } catch (Exception $e) {}
        }
        $form_submitted = true;
        $form_message = cms('contact', 'form_success', "Thank you for reaching out! We've received your inquiry and our farm customer care team will respond within 24 hours.");
    } else {
        $form_message = "Please fill in all required fields.";
    }
}
?>

<?php
$farmPhone = (string)getSetting('farm_phone', '+254 727 585 599');
$farmEmail = (string)getSetting('farm_email', 'info@busiachicken.com');
$phoneDigits = preg_replace('/\D+/', '', $farmPhone) ?: '254727585599';
$whatsappNumber = preg_replace('/\D+/', '', (string)getSetting('farm_whatsapp', '254727585599')) ?: '254727585599';
$socialFacebook = cms('global', 'social_facebook', 'https://facebook.com');
$socialX = cms('global', 'social_x', 'https://x.com');
$socialInstagram = cms('global', 'social_instagram', 'https://instagram.com');

// Subject list comes from Admin → Settings → System Dropdowns → Contact Form
// Subjects, so adding or renaming a subject there changes this form.
if (!function_exists('renderDropdownOptions')) {
    require_once __DIR__ . '/../../Backend/api/dropdowns.php';
}
$subjectOptions = renderDropdownOptions('contact_subjects', $_POST['subject'] ?? null, 'Select a subject...');
if (substr_count($subjectOptions, '<option') <= 1) {
    $subjectOptions = '<option value="">Select a subject...</option>'
        . '<option value="Eggs &amp; Feeds Order">Eggs &amp; Feeds Order</option>'
        . '<option value="Fresh Farm Eggs Supply">Fresh Farm Eggs Supply (Trays / Wholesale)</option>'
        . '<option value="Animal Feeds / Bulk Delivery">Animal Feeds / Bulk Delivery</option>'
        . '<option value="Broilers / Live Birds">Live Birds Order</option>'
        . '<option value="General Inquiry / Visit">General Inquiry / Farm Visit</option>';
}
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('<?php echo htmlspecialchars(cms_img('contact', 'hero_image', '/Frontend/images/adbg.webp'), ENT_QUOTES, 'UTF-8'); ?>');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>Contact</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100"><?php echo cms_html('contact', 'hero_title'); ?></h1>
        <p data-reveal="fade" data-reveal-delay="200"><?php echo cms_text('contact', 'hero_subtitle'); ?></p>
    </div>
</section>

<!-- Contact Content -->
<section class="section-pad bg-white">
    <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:clamp(2.4rem,5vw,4rem);align-items:start;">

        <!-- Contact Information & Both Locations -->
        <div data-reveal="left">
            <span class="eyebrow" style="color:var(--gold-ink);font-weight:800;letter-spacing:0.08em;"><i data-lucide="map-pin" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;"></i> DIRECT CONTACT &amp; HUBS</span>
            <h2 class="section-title">Let's <em>talk</em></h2>
            <p class="lead">Reach out by phone, WhatsApp, email or visit either of our two operational facilities in Busia County.</p>

            <div style="display:grid;gap:1.2rem;margin-top:2rem;">
                <!-- Phone -->
                <div class="p-card" style="display:flex;gap:1.1rem;align-items:flex-start;">
                    <div class="p-icon" style="margin:0;flex-shrink:0;"><i data-lucide="phone" style="width:24px;height:24px;"></i></div>
                    <div>
                        <h4 style="margin-bottom:0.2rem;"><?php echo cms_text('contact', 'phone_label'); ?></h4>
                        <p style="margin-bottom:0.2rem;"><a href="tel:+<?php echo htmlspecialchars($phoneDigits, ENT_QUOTES); ?>" style="color:var(--brand-600);font-weight:700;"><?php echo htmlspecialchars($farmPhone, ENT_QUOTES); ?></a></p>
                        <p style="color:var(--gray-400);font-size:0.9rem;margin:0;"><?php echo cms_text('contact', 'phone_hours'); ?></p>
                    </div>
                </div>

                <!-- Email -->
                <div class="p-card" style="display:flex;gap:1.1rem;align-items:flex-start;">
                    <div class="p-icon" style="margin:0;flex-shrink:0;"><i data-lucide="mail" style="width:24px;height:24px;"></i></div>
                    <div>
                        <h4 style="margin-bottom:0.2rem;">Email</h4>
                        <p style="margin-bottom:0.2rem;"><a href="mailto:<?php echo htmlspecialchars($farmEmail, ENT_QUOTES); ?>" style="color:var(--brand-600);font-weight:700;"><?php echo htmlspecialchars($farmEmail, ENT_QUOTES); ?></a></p>
                        <p style="color:var(--gray-400);font-size:0.9rem;margin:0;"><?php echo cms_text('contact', 'email_note'); ?></p>
                    </div>
                </div>

                <!-- Location 1: Nasira AC Sub-location -->
                <div class="p-card" id="nasira" style="display:flex;gap:1.1rem;align-items:flex-start;border-left:4px solid var(--brand-600);">
                    <div class="p-icon" style="margin:0;flex-shrink:0;"><i data-lucide="building" style="width:24px;height:24px;"></i></div>
                    <div>
                        <span style="font-size:0.72rem;font-weight:800;color:var(--brand-600);text-transform:uppercase;"><?php echo cms_text('contact', 'loc_1_badge'); ?></span>
                        <h4 style="margin-bottom:0.2rem;"><?php echo cms_text('contact', 'loc_1_name'); ?></h4>
                        <p style="color:var(--gray-500);font-size:0.9rem;margin-bottom:0.6rem;"><?php echo cms_text('contact', 'loc_1_address'); ?></p>
                        <p style="font-size:0.82rem;color:var(--gray-400);margin-bottom:0.8rem;"><?php echo cms_text('contact', 'loc_1_hours'); ?></p>
                        <a href="<?php echo htmlspecialchars(cms('contact', 'loc_1_maps', 'https://www.google.com/maps/place/0%C2%B026\'06.0%22N+34%C2%B012\'07.6%22E/@0.4349969,34.1995236,17z/data=!3m1!4b1!4m4!3m3!8m2!3d0.4349969!4d34.2020985?hl=en&entry=ttu&g_ep=EgoyMDI2MTExNi4wIKXMDSoASAFQAw%3D%3D'), ENT_QUOTES); ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm" style="font-size:0.8rem;padding:4px 10px;">
                            <i data-lucide="navigation" style="width:14px;height:14px;"></i> Google Maps Directions
                        </a>
                    </div>
                </div>

                <!-- Location 2: Tangakona Farm -->
                <div class="p-card" id="tangakona" style="display:flex;gap:1.1rem;align-items:flex-start;border-left:4px solid var(--gold-500);">
                    <div class="p-icon" style="margin:0;flex-shrink:0;"><i data-lucide="store" style="width:24px;height:24px;"></i></div>
                    <div>
                        <span style="font-size:0.72rem;font-weight:800;color:var(--gold-600);text-transform:uppercase;"><?php echo cms_text('contact', 'loc_2_badge'); ?></span>
                        <h4 style="margin-bottom:0.2rem;"><?php echo cms_text('contact', 'loc_2_name'); ?></h4>
                        <p style="color:var(--gray-500);font-size:0.9rem;margin-bottom:0.6rem;"><?php echo cms_text('contact', 'loc_2_address'); ?></p>
                        <p style="font-size:0.82rem;color:var(--gray-400);margin-bottom:0.8rem;"><?php echo cms_text('contact', 'loc_2_hours'); ?></p>
                        <a href="<?php echo htmlspecialchars(cms('contact', 'loc_2_maps', 'https://www.google.com/maps?q=0.468734,34.2188207&z=17&hl=en'), ENT_QUOTES); ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm" style="font-size:0.8rem;padding:4px 10px;">
                            <i data-lucide="navigation" style="width:14px;height:14px;"></i> Google Maps Directions
                        </a>
                    </div>
                </div>
            </div>

            <div style="margin-top:2rem;">
                <h4 style="margin-bottom:1rem;font-size:0.95rem;letter-spacing:0.12em;text-transform:uppercase;">Connect With Us</h4>
                <div class="f-socials" style="display:flex;gap:0.7rem;">
                    <a href="<?php echo htmlspecialchars($socialFacebook, ENT_QUOTES); ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
                    <a href="<?php echo htmlspecialchars($socialX, ENT_QUOTES); ?>" target="_blank" rel="noopener" aria-label="Twitter / X"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg></a>
                    <a href="<?php echo htmlspecialchars($socialInstagram, ENT_QUOTES); ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>
                    <a href="https://wa.me/<?php echo htmlspecialchars($whatsappNumber, ENT_QUOTES); ?>?text=<?php echo rawurlencode('Hello Busia Chicken Farm, I would like to order'); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg></a>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div data-reveal="right">
            <div class="p-card" style="padding:clamp(1.8rem,4vw,2.8rem);box-shadow:var(--shadow-lift);">
                <h3 style="margin-bottom:1.6rem;"><?php echo cms_text('contact', 'form_title'); ?></h3>

                <?php if ($form_submitted): ?>
                    <div style="padding:1rem 1.2rem;background:#ECFDF5;border-left:4px solid #059669;color:#065F46;margin-bottom:1.2rem;border-radius:8px;">
                        <strong>Success!</strong> <?php echo htmlspecialchars($form_message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php elseif ($form_message): ?>
                    <div style="padding:1rem 1.2rem;background:#FBF1D8;border-left:4px solid #C9971C;color:#6B4E00;margin-bottom:1.2rem;border-radius:8px;">
                        <strong>Error:</strong> <?php echo htmlspecialchars($form_message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group" style="margin-bottom:1.2rem;">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" id="name" name="name" required class="form-control" placeholder="e.g. John Wandera" style="border-radius:4px;padding:0.8rem 1.2rem;width:100%;">
                    </div>

                    <div class="contact-form-cols" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.2rem;">
                        <div>
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" id="email" name="email" required class="form-control" placeholder="john@example.com" style="border-radius:4px;padding:0.8rem 1.2rem;width:100%;">
                        </div>
                        <div>
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required class="form-control" placeholder="+254 7XX XXX XXX" style="border-radius:4px;padding:0.8rem 1.2rem;width:100%;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:1.2rem;">
                        <label for="subject" class="form-label">Subject *</label>
                        <select id="subject" name="subject" required class="form-control" style="border-radius:4px;padding:0.8rem 1.2rem;background:#fff;width:100%;">
                            <?php echo $subjectOptions; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:1.4rem;">
                        <label for="message" class="form-label">Message *</label>
                        <textarea id="message" name="message" required class="form-control" placeholder="Tell us how many egg trays, feed bags or organic fertiliser you need..." style="min-height:140px;resize:vertical;border-radius:4px;padding:0.9rem 1.2rem;width:100%;"></textarea>
                    </div>

                    <button type="submit" name="contact_submit" value="1" class="btn btn-red" style="width:100%;justify-content:center;" data-magnetic>
                        <span>Send Message</span>
                        <i data-lucide="send" style="width:18px;height:18px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/../includes/footer.php';
?>
