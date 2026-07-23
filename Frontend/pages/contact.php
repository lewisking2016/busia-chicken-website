<?php
/**
 * Contact Us Page
 * Premium Minimalist Redesign
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

<!-- Clean Hero Section -->
<section style="padding: var(--space-4xl) 0 var(--space-3xl); background-image: url('/Frontend/images/download (8).png'); background-size: cover; background-position: center; background-repeat: no-repeat; position: relative; border-bottom: 1px solid rgba(255,255,255,0.12);">
    <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.35);"></div>
    <div class="container" style="position: relative; z-index: 1; max-width: 800px; text-align: center; color: #FFFFFF;">
        <h1 style="margin-bottom: var(--space-lg); color: #FFFFFF;">Get In Touch</h1>
        <p style="font-size: 1.25rem; color: rgba(255,255,255,0.9); margin-bottom: 0;">
            Have questions about our products or want to discuss a bulk order? We're here to help.
        </p>
    </div>
</section>

<!-- Contact Content -->
<section style="padding: var(--space-4xl) 0; background-color: var(--white);">
    <div class="container">
        <div class="grid-2" style="align-items: start;">
            
            <!-- Contact Information -->
            <div>
                <h2 style="margin-bottom: var(--space-2xl);">Contact Information</h2>

                <div style="display: flex; gap: var(--space-lg); margin-bottom: var(--space-xl);">
                    <div style="width: 48px; height: 48px; background: var(--gray-100); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--primary); flex-shrink: 0;">
                        <i data-lucide="phone"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: var(--space-xs);">Phone</h4>
                        <p style="margin-bottom: var(--space-xs);">
                            <a href="tel:+254727585599" style="color: var(--primary); font-weight: 600; font-size: 1.125rem;">+254 727 585 599</a>
                        </p>
                        <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0;">Mon - Fri, 8:00 AM - 6:00 PM EAT</p>
                    </div>
                </div>

                <div style="display: flex; gap: var(--space-lg); margin-bottom: var(--space-xl);">
                    <div style="width: 48px; height: 48px; background: var(--gray-100); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--primary); flex-shrink: 0;">
                        <i data-lucide="mail"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: var(--space-xs);">Email</h4>
                        <p style="margin-bottom: var(--space-xs);">
                            <a href="mailto:info@busiachicken.com" style="color: var(--primary); font-weight: 600; font-size: 1.125rem;">info@busiachicken.com</a>
                        </p>
                        <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0;">We aim to respond within 24 hours</p>
                    </div>
                </div>

                <div style="display: flex; gap: var(--space-lg); margin-bottom: var(--space-xl);">
                    <div style="width: 48px; height: 48px; background: var(--gray-100); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--primary); flex-shrink: 0;">
                        <i data-lucide="map-pin"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: var(--space-xs);">Location</h4>
                        <p style="color: var(--dark); font-weight: 600; margin-bottom: var(--space-xs);">Busia Chicken Farm</p>
                        <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0;">Nasira AC sub-location, Busibwabo Location<br>Busia, Kenya</p>
                    </div>
                </div>

                <!-- Socials (Minimalist) -->
                <div style="margin-top: var(--space-3xl);">
                    <h4 style="margin-bottom: var(--space-lg);">Follow Us</h4>
                    <div style="display: flex; gap: var(--space-md);">
                        <a href="#" style="width: 40px; height: 40px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--gray-600); transition: all 0.2s;">
                            <i data-lucide="facebook" style="width: 18px; height: 18px;"></i>
                        </a>
                        <a href="#" style="width: 40px; height: 40px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--gray-600); transition: all 0.2s;">
                            <i data-lucide="twitter" style="width: 18px; height: 18px;"></i>
                        </a>
                        <a href="#" style="width: 40px; height: 40px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--gray-600); transition: all 0.2s;">
                            <i data-lucide="instagram" style="width: 18px; height: 18px;"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div>
                <div style="background: var(--white); padding: var(--space-3xl); border-radius: var(--radius-lg); border: 1px solid var(--gray-200); box-shadow: var(--shadow-lg);">
                    <h3 style="margin-bottom: var(--space-xl);">Send us a message</h3>

                    <?php if ($form_submitted): ?>
                        <div style="padding: 1rem; background-color: #ECFDF5; border-left: 4px solid var(--success); color: #065F46; margin-bottom: var(--space-lg); border-radius: 4px;">
                            <strong>Success!</strong> <?php echo $form_message; ?>
                        </div>
                    <?php elseif ($form_message): ?>
                        <div style="padding: 1rem; background-color: #FEF2F2; border-left: 4px solid var(--error); color: #991B1B; margin-bottom: var(--space-lg); border-radius: 4px;">
                            <strong>Error:</strong> <?php echo $form_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div style="margin-bottom: var(--space-lg);">
                            <label for="name" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Full Name *</label>
                            <input type="text" id="name" name="name" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); font-size: 1rem; outline: none; transition: border-color 0.2s;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-lg);">
                            <div>
                                <label for="email" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Email Address *</label>
                                <input type="email" id="email" name="email" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); font-size: 1rem; outline: none;">
                            </div>
                            <div>
                                <label for="phone" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); font-size: 1rem; outline: none;">
                            </div>
                        </div>

                        <div style="margin-bottom: var(--space-lg);">
                            <label for="subject" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Subject *</label>
                            <select id="subject" name="subject" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); font-size: 1rem; outline: none; background: var(--white);">
                                <option value="">Select a subject</option>
                                <option value="product-inquiry">Product Inquiry</option>
                                <option value="bulk-order">Bulk Order</option>
                                <option value="partnership">Partnership Opportunity</option>
                                <option value="support">Support</option>
                            </select>
                        </div>

                        <div style="margin-bottom: var(--space-xl);">
                            <label for="message" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Message *</label>
                            <textarea id="message" name="message" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-200); border-radius: var(--radius-sm); font-size: 1rem; outline: none; min-height: 150px; resize: vertical;"></textarea>
                        </div>

                        <button type="submit" name="contact_submit" value="1" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            Send Message
                            <i data-lucide="send" style="width: 18px; height: 18px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section style="padding: var(--space-4xl) 0; background-color: var(--gray-50); border-top: 1px solid var(--gray-200);">
    <div class="container">
        <div class="section-header">
            <h2>Find Us</h2>
            <p>Visit our farm in Busia. We're open for scheduled visits and pickups.</p>
        </div>
        <div style="border-radius: var(--radius-md); overflow: hidden; height: 450px; box-shadow: var(--shadow-md); border: 1px solid var(--gray-200);">
            <iframe 
                width="100%" 
                height="100%" 
                frameborder="0" 
                style="border:0" 
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