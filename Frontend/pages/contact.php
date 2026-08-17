<?php
/**
 * Contact Us Page � Premium Redesign
 */
declare(strict_types=1);

$path_prefix = '../';
$page_title = 'Contact Us - Busia Chicken Farm';

include '../includes/header.php';

// Handle form submission
$form_submitted = false;
$form_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $phone && $subject && $message) {
        $form_submitted = true;
        $form_message = "Thank you for reaching out! We'll get back to you within 24 hours.";
    } else {
        $form_message = "Please fill in all fields.";
    }
}
?>

<!-- Page Hero -->
<section class="page-hero" style="background-image:url('/Frontend/images/download (8).png');">
    <div class="container">
        <nav class="breadcrumb" data-reveal="fade"><a href="/">Home</a><span class="sep">/</span><span>Contact</span></nav>
        <h1 data-reveal="fade" data-reveal-delay="100">Get In <em>Touch</em></h1>
        <p data-reveal="fade" data-reveal-delay="200">Have questions about our products or want to discuss a bulk order? We're here to help.</p>
    </div>
</section>

<!-- Contact Content -->
<section class="section-pad bg-white">
    <div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:clamp(2.4rem,5vw,4rem);align-items:start;">

        <!-- Contact Information -->
        <div data-reveal="left">
            <span class="eyebrow">Contact Information</span>
            <h2 class="section-title">Let's <em>talk</em></h2>
            <p class="lead">Reach out by phone, email or visit the farm � we respond within 24 hours on all channels.</p>

            <div style="display:grid;gap:1.2rem;margin-top:2rem;">
                <div class="p-card" style="display:flex;gap:1.1rem;align-items:flex-start;">
                    <div class="p-icon" style="margin:0;flex-shrink:0;"><i data-lucide="phone" style="width:24px;height:24px;"></i></div>
                    <div>
                        <h4 style="margin-bottom:0.2rem;">Phone</h4>
                        <p style="margin-bottom:0.2rem;"><a href="tel:+254727585599" style="color:var(--brand-600);font-weight:700;">+254 727 585 599</a></p>
                        <p style="color:var(--gray-400);font-size:0.9rem;margin:0;">Mon � Fri, 8:00 AM � 6:00 PM EAT</p>
                    </div>
                </div>

                <div class="p-card" style="display:flex;gap:1.1rem;align-items:flex-start;">
                    <div class="p-icon" style="margin:0;flex-shrink:0;"><i data-lucide="mail" style="width:24px;height:24px;"></i></div>
                    <div>
                        <h4 style="margin-bottom:0.2rem;">Email</h4>
                        <p style="margin-bottom:0.2rem;"><a href="mailto:info@busiachicken.com" style="color:var(--brand-600);font-weight:700;">info@busiachicken.com</a></p>
                        <p style="color:var(--gray-400);font-size:0.9rem;margin:0;">We aim to respond within 24 hours</p>
                    </div>
                </div>

                <div class="p-card" style="display:flex;gap:1.1rem;align-items:flex-start;">
                    <div class="p-icon" style="margin:0;flex-shrink:0;"><i data-lucide="map-pin" style="width:24px;height:24px;"></i></div>
                    <div>
                        <h4 style="margin-bottom:0.2rem;">Location</h4>
                        <p style="margin-bottom:0.2rem;font-weight:700;color:var(--brand-950);">Busia Chicken Farm</p>
                        <p style="color:var(--gray-400);font-size:0.9rem;margin:0;">Nasira AC sub-location, Busibwabo Location, Busia, Kenya</p>
                    </div>
                </div>
            </div>

            <div style="margin-top:2rem;">
                <h4 style="margin-bottom:1rem;font-size:0.95rem;letter-spacing:0.12em;text-transform:uppercase;">Follow Us</h4>
                <div class="f-socials" style="display:flex;gap:0.7rem;">
                    <a href="#" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
                    <a href="#" aria-label="Twitter / X"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg></a>
                    <a href="#" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>
                    <a href="#" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg></a>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div data-reveal="right">
            <div class="p-card" style="padding:clamp(1.8rem,4vw,2.8rem);box-shadow:var(--shadow-lift);">
                <h3 style="margin-bottom:1.6rem;">Send us a message</h3>

                <?php if ($form_submitted): ?>
                    <div style="padding:1rem 1.2rem;background:#ECFDF5;border-left:4px solid #059669;color:#065F46;margin-bottom:1.2rem;border-radius:8px;">
                        <strong>Success!</strong> <?php echo $form_message; ?>
                    </div>
                <?php elseif ($form_message): ?>
                    <div style="padding:1rem 1.2rem;background:#FEF2F2;border-left:4px solid #DC2626;color:#991B1B;margin-bottom:1.2rem;border-radius:8px;">
                        <strong>Error:</strong> <?php echo $form_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" id="name" name="name" required class="form-control" style="border-radius:4px;padding:0.8rem 1.2rem;">
                    </div>

                    <div class="contact-form-cols" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                        <div>
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" id="email" name="email" required class="form-control" style="border-radius:4px;padding:0.8rem 1.2rem;">
                        </div>
                        <div>
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required class="form-control" style="border-radius:4px;padding:0.8rem 1.2rem;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Subject *</label>
                        <select id="subject" name="subject" required class="form-control" style="border-radius:4px;padding:0.8rem 1.2rem;background:#fff;">
                            <?php
                            require_once __DIR__ . '/../../Backend/api/dropdowns.php';
                            echo renderDropdownOptions('contact_subjects', null, 'Select a subject');
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Message *</label>
                        <textarea id="message" name="message" required class="form-control" style="min-height:140px;resize:vertical;border-radius:16px;padding:0.9rem 1.2rem;"></textarea>
                    </div>

                    <button type="submit" name="contact_submit" value="1" class="btn btn-primary" style="width:100%;justify-content:center;" data-magnetic>
                        Send Message
                        <i data-lucide="send" style="width:18px;height:18px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Map -->
<section class="section-pad" style="background:var(--cream-50);">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow centered">Find Us</span>
            <h2 class="section-title">Visit our farm in <em>Busia</em></h2>
            <p class="lead">We're open for scheduled visits and pickups.</p>
        </div>
        <div class="img-frame" data-reveal="zoom" style="height:450px;box-shadow:var(--shadow-lift);">
            <iframe
                width="100%"
                height="100%"
                frameborder="0"
                style="border:0;display:block;"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3981.5234567890!2d34.1234567!3d0.4567890!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sBusia%20Chicken%20Farm!5e0!3m2!1sen!2ske!4v1234567890"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
