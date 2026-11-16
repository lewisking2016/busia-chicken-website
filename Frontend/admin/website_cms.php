<?php
/**
 * Website CMS & Slider Manager — Busia Chicken Limited
 * Allows administrators to edit homepage hero slides, photos, announcement banner,
 * brand slogan ("The Chicken People"), and Google Business locations (Nasira & Tangakona).
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $temp_dir = sys_get_temp_dir();
    if (is_writable($temp_dir)) session_save_path($temp_dir);
    session_start();
}

$page_title = 'Website CMS & Sliders - Admin';
include __DIR__ . '/includes/admin_header.php';

// Auth check
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    echo '<div style="padding:40px;text-align:center;"><h2>Access Denied</h2><p>Only administrators and farm managers can edit website content.</p></div>';
    include __DIR__ . '/includes/admin_footer.php';
    exit;
}

$pdo = function_exists('getDB') ? getDB() : (function_exists('getDatabaseConnection') ? getDatabaseConnection() : null);

$message = '';
$error = '';
$tab = $_GET['tab'] ?? 'sliders';
$editSlideId = isset($_GET['edit_slide']) ? (int)$_GET['edit_slide'] : 0;

// Create uploads folder if not exists
$uploadDir = __DIR__ . '/../images/uploads/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $action = $_POST['_action'] ?? '';

    // 1. SAVE SLIDE (ADD OR UPDATE)
    if ($action === 'save_slide') {
        $slideId = (int)($_POST['slide_id'] ?? 0);
        $order = (int)($_POST['slide_order'] ?? 1);
        $badge = trim($_POST['badge_text'] ?? '');
        $titleMain = trim($_POST['title_main'] ?? '');
        $titleEm = trim($_POST['title_em'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $cta1Text = trim($_POST['cta_primary_text'] ?? 'Explore Products');
        $cta1Link = trim($_POST['cta_primary_link'] ?? '/Frontend/pages/shop.php');
        $cta2Text = trim($_POST['cta_secondary_text'] ?? 'Our Story');
        $cta2Link = trim($_POST['cta_secondary_link'] ?? '/Frontend/pages/about.php');
        $s1Val = trim($_POST['stat1_val'] ?? '');
        $s1Suf = trim($_POST['stat1_suffix'] ?? '');
        $s1Lbl = trim($_POST['stat1_label'] ?? '');
        $s2Val = trim($_POST['stat2_val'] ?? '');
        $s2Suf = trim($_POST['stat2_suffix'] ?? '');
        $s2Lbl = trim($_POST['stat2_label'] ?? '');
        $s3Val = trim($_POST['stat3_val'] ?? '');
        $s3Suf = trim($_POST['stat3_suffix'] ?? '');
        $s3Lbl = trim($_POST['stat3_label'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '/Frontend/images/adbg.png');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Handle File Upload if provided
        if (!empty($_FILES['slide_file']['name']) && $_FILES['slide_file']['error'] === UPLOAD_ERR_OK) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $fileInfo = pathinfo($_FILES['slide_file']['name']);
            $ext = strtolower($fileInfo['extension'] ?? '');

            if (in_array($ext, $allowedExts, true)) {
                $safeName = 'slide_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $uploadDir . $safeName;
                if (move_uploaded_file($_FILES['slide_file']['tmp_name'], $destPath)) {
                    $imageUrl = '/Frontend/images/uploads/' . $safeName;
                } else {
                    $error = 'Failed to move uploaded slider photo.';
                }
            } else {
                $error = 'Invalid image format. Allowed formats: JPG, PNG, WEBP.';
            }
        }

        if (empty($error)) {
            try {
                if ($slideId > 0) {
                    $stmt = $pdo->prepare("UPDATE website_sliders SET 
                        slide_order = ?, badge_text = ?, title_main = ?, title_em = ?, subtitle = ?, 
                        cta_primary_text = ?, cta_primary_link = ?, cta_secondary_text = ?, cta_secondary_link = ?, 
                        stat1_val = ?, stat1_suffix = ?, stat1_label = ?, 
                        stat2_val = ?, stat2_suffix = ?, stat2_label = ?, 
                        stat3_val = ?, stat3_suffix = ?, stat3_label = ?, 
                        image_url = ?, is_active = ? 
                        WHERE id = ?");
                    $stmt->execute([
                        $order, $badge, $titleMain, $titleEm, $subtitle,
                        $cta1Text, $cta1Link, $cta2Text, $cta2Link,
                        $s1Val, $s1Suf, $s1Lbl,
                        $s2Val, $s2Suf, $s2Lbl,
                        $s3Val, $s3Suf, $s3Lbl,
                        $imageUrl, $isActive, $slideId
                    ]);
                    $message = 'Hero slide updated successfully!';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO website_sliders 
                        (slide_order, badge_text, title_main, title_em, subtitle, 
                        cta_primary_text, cta_primary_link, cta_secondary_text, cta_secondary_link, 
                        stat1_val, stat1_suffix, stat1_label, 
                        stat2_val, stat2_suffix, stat2_label, 
                        stat3_val, stat3_suffix, stat3_label, 
                        image_url, is_active)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $order, $badge, $titleMain, $titleEm, $subtitle,
                        $cta1Text, $cta1Link, $cta2Text, $cta2Link,
                        $s1Val, $s1Suf, $s1Lbl,
                        $s2Val, $s2Suf, $s2Lbl,
                        $s3Val, $s3Suf, $s3Lbl,
                        $imageUrl, $isActive
                    ]);
                    $message = 'New hero slide added successfully!';
                }
                $editSlideId = 0;
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }

    // 2. DELETE SLIDE
    if ($action === 'delete_slide') {
        $slideId = (int)($_POST['slide_id'] ?? 0);
        if ($slideId > 0) {
            try {
                $pdo->prepare("DELETE FROM website_sliders WHERE id = ?")->execute([$slideId]);
                $message = 'Hero slide deleted successfully.';
            } catch (Exception $e) {
                $error = 'Failed to delete slide: ' . $e->getMessage();
            }
        }
    }

    // 3. TOGGLE SLIDE STATUS
    if ($action === 'toggle_slide') {
        $slideId = (int)($_POST['slide_id'] ?? 0);
        if ($slideId > 0) {
            try {
                $pdo->prepare("UPDATE website_sliders SET is_active = NOT is_active WHERE id = ?")->execute([$slideId]);
                $message = 'Slide visibility toggled.';
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }

    // 4. SAVE GENERAL CONTENT & LOCATIONS
    if ($action === 'save_content') {
        $settingsToSave = [
            'farm_name' => trim($_POST['farm_name'] ?? 'Busia Chicken Limited'),
            'farm_slogan' => trim($_POST['farm_slogan'] ?? 'The Chicken People'),
            'farm_phone' => trim($_POST['farm_phone'] ?? '+254 727 585 599'),
            'farm_email' => trim($_POST['farm_email'] ?? 'info@busiachicken.com'),
            'farm_whatsapp' => trim($_POST['farm_whatsapp'] ?? '+254727585599'),
            'announcement_bar' => trim($_POST['announcement_bar'] ?? ''),
            'show_announcement' => isset($_POST['show_announcement']) ? '1' : '0',
            'location_nasira' => trim($_POST['location_nasira'] ?? ''),
            'hours_nasira' => trim($_POST['hours_nasira'] ?? ''),
            'google_maps_nasira' => trim($_POST['google_maps_nasira'] ?? ''),
            'location_tangakona' => trim($_POST['location_tangakona'] ?? ''),
            'hours_tangakona' => trim($_POST['hours_tangakona'] ?? ''),
            'google_maps_tangakona' => trim($_POST['google_maps_tangakona'] ?? ''),
        ];

        try {
            foreach ($settingsToSave as $k => $v) {
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$k, $v, $v]);
            }
            $message = 'Website content and Google Business locations updated successfully!';
        } catch (Exception $e) {
            $error = 'Error saving content: ' . $e->getMessage();
        }
        $tab = 'content';
    }

    // 5. ADD TESTIMONIAL
    if ($action === 'save_testimonial') {
        $cName = trim($_POST['customer_name'] ?? '');
        $cRole = trim($_POST['customer_role'] ?? 'Farmer');
        $cRating = (int)($_POST['rating'] ?? 5);
        $cContent = trim($_POST['content'] ?? '');
        $cApproved = isset($_POST['is_approved']) ? 1 : 0;

        if ($cName && $cContent) {
            try {
                $stmt = $pdo->prepare("INSERT INTO testimonials (customer_name, customer_role, rating, content, is_approved) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$cName, $cRole, $cRating, $cContent, $cApproved]);
                $message = 'Testimonial added successfully!';
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        } else {
            $error = 'Please provide customer name and testimonial text.';
        }
        $tab = 'testimonials';
    }
}

// Fetch current slides
$allSlides = [];
$slideEdit = null;
if ($pdo) {
    try {
        $allSlides = $pdo->query("SELECT * FROM website_sliders ORDER BY slide_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
        if ($editSlideId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM website_sliders WHERE id = ?");
            $stmt->execute([$editSlideId]);
            $slideEdit = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {}
}

// Fetch site settings
$currentSettings = [];
if ($pdo) {
    try {
        $currentSettings = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {}
}

// Fetch testimonials
$allTestimonials = [];
if ($pdo) {
    try {
        $allTestimonials = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:14px;">
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
            <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;">Website CMS &amp; Content Manager</h1>
            <span style="background:#dcfce7;color:#166534;font-size:0.75rem;font-weight:700;padding:3px 9px;border-radius:4px;text-transform:uppercase;">Live Frontend</span>
        </div>
        <p style="margin:0;color:#64748b;font-size:0.92rem;">Control homepage hero sliders, change photos, edit the announcement banner, slogan, and Google Business locations.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="/" target="_blank" class="btn btn-outline btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
            <i data-lucide="external-link" style="width:15px;height:15px;"></i> View Live Website
        </a>
    </div>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:8px;color:#166534;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-weight:600;">
    <i data-lucide="check-circle-2" style="width:20px;height:20px;flex-shrink:0;"></i> <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:8px;color:#b91c1c;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-weight:600;">
    <i data-lucide="alert-circle" style="width:20px;height:20px;flex-shrink:0;"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<!-- Tabs Bar -->
<div style="display:flex;gap:6px;background:#f1f5f9;padding:6px;border-radius:10px;margin-bottom:24px;overflow-x:auto;">
    <a href="?tab=sliders" style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.88rem;<?php echo $tab === 'sliders' ? 'background:#fff;color:var(--admin-primary);box-shadow:0 1px 4px rgba(0,0,0,0.06);' : 'color:#64748b;'; ?>">
        <i data-lucide="image" style="width:16px;height:16px;"></i> Hero Sliders &amp; Photos
    </a>
    <a href="?tab=content" style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.88rem;<?php echo $tab === 'content' ? 'background:#fff;color:var(--admin-primary);box-shadow:0 1px 4px rgba(0,0,0,0.06);' : 'color:#64748b;'; ?>">
        <i data-lucide="map-pin" style="width:16px;height:16px;"></i> Brand, Banner &amp; Two Locations
    </a>
    <a href="?tab=testimonials" style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.88rem;<?php echo $tab === 'testimonials' ? 'background:#fff;color:var(--admin-primary);box-shadow:0 1px 4px rgba(0,0,0,0.06);' : 'color:#64748b;'; ?>">
        <i data-lucide="message-square" style="width:16px;height:16px;"></i> Facebook Reviews &amp; Testimonials
    </a>
</div>

<!-- ══════════════ TAB 1: HERO SLIDERS & PHOTOS ══════════════ -->
<?php if ($tab === 'sliders'): ?>
<div style="display:grid;grid-template-columns:1fr 420px;gap:24px;align-items:start;">

    <!-- Left: Slide List -->
    <div>
        <div class="admin-card" style="padding:22px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.15rem;">Active Hero Slides (<?php echo count($allSlides); ?>)</h3>
                <a href="?tab=sliders" class="btn btn-outline btn-sm"><i data-lucide="plus" style="width:14px;height:14px;"></i> Add New Slide</a>
            </div>

            <?php if (empty($allSlides)): ?>
                <p style="color:#64748b;text-align:center;padding:30px;">No slides found in the database. Add one on the right!</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:14px;">
                <?php foreach ($allSlides as $s): ?>
                    <div style="display:flex;gap:16px;align-items:center;padding:14px;border:1px solid var(--admin-border);border-radius:10px;background:<?php echo $editSlideId === (int)$s['id'] ? '#f0fdf4' : '#fff'; ?>;transition:all 0.2s;">
                        <!-- Slide preview thumbnail -->
                        <div style="width:110px;height:75px;border-radius:6px;overflow:hidden;flex-shrink:0;position:relative;background:#000;">
                            <img src="<?php echo htmlspecialchars($s['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:100%;height:100%;object-fit:cover;opacity:0.85;">
                            <span style="position:absolute;top:4px;left:4px;background:rgba(0,0,0,0.7);color:#fff;font-size:0.65rem;font-weight:800;padding:2px 6px;border-radius:3px;">
                                #<?php echo $s['slide_order']; ?>
                            </span>
                        </div>

                        <!-- Info -->
                        <div style="flex-grow:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;">
                                <span style="font-size:0.75rem;font-weight:700;color:var(--admin-primary);text-transform:uppercase;"><?php echo htmlspecialchars($s['badge_text'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span style="font-size:0.7rem;padding:1px 6px;border-radius:3px;<?php echo $s['is_active'] ? 'background:#dcfce7;color:#166534;' : 'background:#fee2e2;color:#991b1b;'; ?>">
                                    <?php echo $s['is_active'] ? 'Active' : 'Disabled'; ?>
                                </span>
                            </div>
                            <strong style="font-size:0.95rem;color:var(--admin-text-heading);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo htmlspecialchars($s['title_main'] . ' ' . $s['title_em'], ENT_QUOTES, 'UTF-8'); ?>
                            </strong>
                            <p style="margin:2px 0 0;font-size:0.82rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo htmlspecialchars($s['subtitle'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>

                        <!-- Actions -->
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="_action" value="toggle_slide">
                                <input type="hidden" name="slide_id" value="<?php echo $s['id']; ?>">
                                <button type="submit" class="btn btn-outline btn-sm" title="Toggle visibility" style="padding:6px 9px;">
                                    <i data-lucide="<?php echo $s['is_active'] ? 'eye' : 'eye-off'; ?>" style="width:14px;height:14px;"></i>
                                </button>
                            </form>
                            <a href="?tab=sliders&edit_slide=<?php echo $s['id']; ?>" class="btn btn-outline btn-sm" style="padding:6px 10px;">
                                <i data-lucide="edit-2" style="width:14px;height:14px;"></i> Edit
                            </a>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this slide?');">
                                <input type="hidden" name="_action" value="delete_slide">
                                <input type="hidden" name="slide_id" value="<?php echo $s['id']; ?>">
                                <button type="submit" class="btn btn-outline btn-sm" style="padding:6px 9px;color:#dc2626;" title="Delete slide">
                                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Add / Edit Form -->
    <div class="admin-card" style="padding:22px;">
        <h3 style="margin:0 0 16px;font-family:'Outfit',sans-serif;font-size:1.15rem;">
            <?php echo $slideEdit ? 'Edit Slide #' . $slideEdit['slide_order'] : 'Create New Hero Slide'; ?>
        </h3>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_action" value="save_slide">
            <input type="hidden" name="slide_id" value="<?php echo $slideEdit['id'] ?? 0; ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label class="form-label">Order Priority *</label>
                    <input type="number" name="slide_order" min="1" max="99" required value="<?php echo htmlspecialchars((string)($slideEdit['slide_order'] ?? 1), ENT_QUOTES); ?>" class="form-control">
                </div>
                <div>
                    <label class="form-label">Visibility</label>
                    <label style="display:flex;align-items:center;gap:8px;padding-top:8px;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" <?php echo (!isset($slideEdit) || !empty($slideEdit['is_active'])) ? 'checked' : ''; ?>>
                        <span style="font-weight:600;font-size:0.88rem;">Show on Website</span>
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Eyebrow / Badge Text *</label>
                <input type="text" name="badge_text" required value="<?php echo htmlspecialchars($slideEdit['badge_text'] ?? 'Welcome to Busia Chicken Farm', ENT_QUOTES); ?>" class="form-control" placeholder="e.g. The Egg People">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label class="form-label">Main Heading *</label>
                    <input type="text" name="title_main" required value="<?php echo htmlspecialchars($slideEdit['title_main'] ?? 'Quality Poultry for', ENT_QUOTES); ?>" class="form-control">
                </div>
                <div>
                    <label class="form-label">Italic / Gold Accent *</label>
                    <input type="text" name="title_em" required value="<?php echo htmlspecialchars($slideEdit['title_em'] ?? 'East Africa', ENT_QUOTES); ?>" class="form-control">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Subtitle Paragraph *</label>
                <textarea name="subtitle" rows="3" required class="form-control" style="font-size:0.86rem;"><?php echo htmlspecialchars($slideEdit['subtitle'] ?? 'Premium broilers, layers and day-old chicks — raised with care, delivered with confidence.', ENT_QUOTES); ?></textarea>
            </div>

            <!-- Slide Photo Upload & Selector -->
            <div style="padding:14px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;margin-bottom:14px;">
                <label class="form-label" style="display:flex;align-items:center;gap:6px;">
                    <i data-lucide="upload" style="width:16px;height:16px;"></i> Upload Slide Photo
                </label>
                <input type="file" name="slide_file" accept="image/*" class="form-control" style="padding:6px;background:#fff;margin-bottom:8px;">
                <small style="display:block;color:#64748b;margin-bottom:8px;">Or select existing image path:</small>
                <select name="image_url" class="form-control" style="background:#fff;">
                    <?php
                    $commonImages = [
                        '/Frontend/images/adbg.png' => 'Farm Feed & Chickens (adbg.png)',
                        '/Frontend/images/farm-fresh-eggs.jpg' => 'Farm Fresh Eggs (farm-fresh-eggs.jpg)',
                        '/Frontend/images/download (2).png' => 'Team & Operations (download 2)',
                        '/Frontend/images/download (4).png' => 'Layer Hens & Crates (download 4)',
                        '/Frontend/images/broiler-chickens.jpg' => 'Broiler Flock (broiler-chickens.jpg)',
                        '/Frontend/images/day-old-chicks.jpg' => 'Day-Old Chicks (day-old-chicks.jpg)',
                        '/Frontend/images/farm-exterior.jpg' => 'Farm Exterior (farm-exterior.jpg)',
                        '/Frontend/images/poultry-feed.jpg' => 'Formulated Feeds (poultry-feed.jpg)',
                    ];
                    $curImg = $slideEdit['image_url'] ?? '/Frontend/images/adbg.png';
                    foreach ($commonImages as $path => $lbl): ?>
                        <option value="<?php echo $path; ?>" <?php echo $curImg === $path ? 'selected' : ''; ?>><?php echo $lbl; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($curImg): ?>
                <div style="margin-top:8px;font-size:0.75rem;color:#475569;">
                    Current: <code><?php echo htmlspecialchars($curImg, ENT_QUOTES); ?></code>
                </div>
                <?php endif; ?>
            </div>

            <!-- Buttons -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label class="form-label">Primary Button Text</label>
                    <input type="text" name="cta_primary_text" value="<?php echo htmlspecialchars($slideEdit['cta_primary_text'] ?? 'Explore Products', ENT_QUOTES); ?>" class="form-control">
                </div>
                <div>
                    <label class="form-label">Primary Link</label>
                    <input type="text" name="cta_primary_link" value="<?php echo htmlspecialchars($slideEdit['cta_primary_link'] ?? '/Frontend/pages/shop.php', ENT_QUOTES); ?>" class="form-control">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label class="form-label">Secondary Button Text</label>
                    <input type="text" name="cta_secondary_text" value="<?php echo htmlspecialchars($slideEdit['cta_secondary_text'] ?? 'Our Story', ENT_QUOTES); ?>" class="form-control">
                </div>
                <div>
                    <label class="form-label">Secondary Link</label>
                    <input type="text" name="cta_secondary_link" value="<?php echo htmlspecialchars($slideEdit['cta_secondary_link'] ?? '/Frontend/pages/about.php', ENT_QUOTES); ?>" class="form-control">
                </div>
            </div>

            <!-- 3 Stat Metrics -->
            <label class="form-label" style="font-weight:700;color:var(--admin-text-heading);margin-bottom:8px;display:block;">
                3 Bottom Stat Metrics (e.g. 100k, 5000+, 13 Years)
            </label>
            <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:8px;margin-bottom:8px;">
                <input type="text" name="stat1_val" placeholder="Val (100)" value="<?php echo htmlspecialchars($slideEdit['stat1_val'] ?? '100', ENT_QUOTES); ?>" class="form-control">
                <input type="text" name="stat1_suffix" placeholder="Suffix (k+)" value="<?php echo htmlspecialchars($slideEdit['stat1_suffix'] ?? 'k', ENT_QUOTES); ?>" class="form-control">
                <input type="text" name="stat1_label" placeholder="Label" value="<?php echo htmlspecialchars($slideEdit['stat1_label'] ?? 'Chickens raised annually', ENT_QUOTES); ?>" class="form-control">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:8px;margin-bottom:8px;">
                <input type="text" name="stat2_val" placeholder="Val (5000)" value="<?php echo htmlspecialchars($slideEdit['stat2_val'] ?? '5000', ENT_QUOTES); ?>" class="form-control">
                <input type="text" name="stat2_suffix" placeholder="Suffix (+)" value="<?php echo htmlspecialchars($slideEdit['stat2_suffix'] ?? '+', ENT_QUOTES); ?>" class="form-control">
                <input type="text" name="stat2_label" placeholder="Label" value="<?php echo htmlspecialchars($slideEdit['stat2_label'] ?? 'Happy customers', ENT_QUOTES); ?>" class="form-control">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:8px;margin-bottom:18px;">
                <input type="text" name="stat3_val" placeholder="Val (13)" value="<?php echo htmlspecialchars($slideEdit['stat3_val'] ?? '13', ENT_QUOTES); ?>" class="form-control">
                <input type="text" name="stat3_suffix" placeholder="Suffix (+)" value="<?php echo htmlspecialchars($slideEdit['stat3_suffix'] ?? '+', ENT_QUOTES); ?>" class="form-control">
                <input type="text" name="stat3_label" placeholder="Label" value="<?php echo htmlspecialchars($slideEdit['stat3_label'] ?? 'Years in business', ENT_QUOTES); ?>" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;">
                <i data-lucide="check" style="width:18px;height:18px;"></i> <?php echo $slideEdit ? 'Update Hero Slide' : 'Save & Publish Slide'; ?>
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ══════════════ TAB 2: BRAND, BANNER & TWO LOCATIONS ══════════════ -->
<?php if ($tab === 'content'): ?>
<div class="admin-card" style="padding:26px;max-width:880px;">
    <h3 style="margin:0 0 8px;font-family:'Outfit',sans-serif;font-size:1.2rem;">Brand Identity, Top Announcement &amp; Google Business Locations</h3>
    <p style="color:#64748b;font-size:0.9rem;margin:0 0 24px;">Configure the official brand slogan, top announcement banner, and the two facilities in Busia County.</p>

    <form method="POST">
        <input type="hidden" name="_action" value="save_content">

        <!-- Slogan & Name -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div>
                <label class="form-label">Platform Company Name *</label>
                <input type="text" name="farm_name" required value="<?php echo htmlspecialchars($currentSettings['farm_name'] ?? 'Busia Chicken Limited', ENT_QUOTES); ?>" class="form-control">
            </div>
            <div>
                <label class="form-label">Official Slogan *</label>
                <input type="text" name="farm_slogan" required value="<?php echo htmlspecialchars($currentSettings['farm_slogan'] ?? 'The Chicken People', ENT_QUOTES); ?>" class="form-control">
            </div>
        </div>

        <!-- Announcement Banner -->
        <div style="padding:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;margin-bottom:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <strong style="color:#166534;font-size:0.95rem;">Top Announcement Banner</strong>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                    <input type="checkbox" name="show_announcement" value="1" <?php echo ($currentSettings['show_announcement'] ?? '1') === '1' ? 'checked' : ''; ?>>
                    <span style="font-size:0.85rem;font-weight:700;color:#166534;">Enable Banner</span>
                </label>
            </div>
            <input type="text" name="announcement_bar" value="<?php echo htmlspecialchars($currentSettings['announcement_bar'] ?? '🐣 Fresh Day-Old Chicks & Grade-A Farm Eggs Available Daily in Nasira & Tangakona! Call +254 727 585 599', ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
            <small style="color:#15803d;display:block;margin-top:6px;">Shown at the very top of all public pages on mobile and desktop.</small>
        </div>

        <!-- Contact Numbers -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:24px;">
            <div>
                <label class="form-label">Phone Hotline</label>
                <input type="text" name="farm_phone" value="<?php echo htmlspecialchars($currentSettings['farm_phone'] ?? '+254 727 585 599', ENT_QUOTES); ?>" class="form-control">
            </div>
            <div>
                <label class="form-label">WhatsApp Number</label>
                <input type="text" name="farm_whatsapp" value="<?php echo htmlspecialchars($currentSettings['farm_whatsapp'] ?? '+254727585599', ENT_QUOTES); ?>" class="form-control">
            </div>
            <div>
                <label class="form-label">Email Address</label>
                <input type="email" name="farm_email" value="<?php echo htmlspecialchars($currentSettings['farm_email'] ?? 'info@busiachicken.com', ENT_QUOTES); ?>" class="form-control">
            </div>
        </div>

        <!-- Location 1: Nasira AC Sub-location -->
        <div style="padding:18px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:20px;background:#fafafa;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <i data-lucide="building" style="color:var(--admin-primary);width:20px;height:20px;"></i>
                <h4 style="margin:0;font-size:1.05rem;">Location 1: Nasira AC Sub-location (Farm HQ &amp; Hatchery)</h4>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Physical Address</label>
                <input type="text" name="location_nasira" value="<?php echo htmlspecialchars($currentSettings['location_nasira'] ?? 'Nasira AC Sub-location, Busibwabo Location, Matayos Sub-County, Busia, Kenya', ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label class="form-label">Operating Hours</label>
                    <input type="text" name="hours_nasira" value="<?php echo htmlspecialchars($currentSettings['hours_nasira'] ?? 'Mon - Sat: 7:00 AM - 6:00 PM | Sun: 8:00 AM - 1:00 PM', ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
                </div>
                <div>
                    <label class="form-label">Google Maps Link</label>
                    <input type="text" name="google_maps_nasira" value="<?php echo htmlspecialchars($currentSettings['google_maps_nasira'] ?? 'https://maps.google.com/?q=Nasira+AC+Sub-location+Busia+Kenya', ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
                </div>
            </div>
        </div>

        <!-- Location 2: Tangakona Depot -->
        <div style="padding:18px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:24px;background:#fafafa;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <i data-lucide="store" style="color:var(--gold-600);width:20px;height:20px;"></i>
                <h4 style="margin:0;font-size:1.05rem;">Location 2: Tangakona Branch (Sales &amp; Feed Depot)</h4>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Physical Address</label>
                <input type="text" name="location_tangakona" value="<?php echo htmlspecialchars($currentSettings['location_tangakona'] ?? 'Tangakona Junction / Market along Busia-Kisumu Highway, Busia County, Kenya', ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label class="form-label">Operating Hours</label>
                    <input type="text" name="hours_tangakona" value="<?php echo htmlspecialchars($currentSettings['hours_tangakona'] ?? 'Mon - Sat: 8:00 AM - 6:30 PM | Direct pick-up point', ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
                </div>
                <div>
                    <label class="form-label">Google Maps Link</label>
                    <input type="text" name="google_maps_tangakona" value="<?php echo htmlspecialchars($currentSettings['google_maps_tangakona'] ?? 'https://maps.google.com/?q=Tangakona+Busia+Kenya', ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="padding:12px 28px;">
            <i data-lucide="save" style="width:18px;height:18px;"></i> Save Changes
        </button>
    </form>
</div>
<?php endif; ?>

<!-- ══════════════ TAB 3: TESTIMONIALS & REVIEWS ══════════════ -->
<?php if ($tab === 'testimonials'): ?>
<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">
    <div class="admin-card" style="padding:22px;">
        <h3 style="margin:0 0 8px;font-family:'Outfit',sans-serif;font-size:1.15rem;">Approved Reviews (Facebook • Eggs Only Focus)</h3>
        <p style="margin:0 0 16px;color:#64748b;font-size:0.85rem;">Per the official transcript requirements: <em>Extract testimonials from Facebook and base mostly on eggs only.</em></p>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($allTestimonials as $t): ?>
            <div style="padding:14px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <div>
                        <strong style="color:var(--admin-text-heading);font-size:0.95rem;"><?php echo htmlspecialchars($t['customer_name'], ENT_QUOTES); ?></strong>
                        <span style="color:#64748b;font-size:0.8rem;margin-left:6px;">(<?php echo htmlspecialchars($t['customer_role'] ?? 'Farmer', ENT_QUOTES); ?>)</span>
                    </div>
                    <span style="color:#eab308;font-size:0.9rem;">★★★★★</span>
                </div>
                <p style="margin:0;color:#334155;font-size:0.88rem;line-height:1.5;">&ldquo;<?php echo htmlspecialchars($t['content'], ENT_QUOTES); ?>&rdquo;</p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Testimonial -->
    <div class="admin-card" style="padding:22px;">
        <h3 style="margin:0 0 16px;font-family:'Outfit',sans-serif;font-size:1.15rem;">Add Facebook Review</h3>
        <form method="POST">
            <input type="hidden" name="_action" value="save_testimonial">
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Customer / Business Name *</label>
                <input type="text" name="customer_name" required placeholder="e.g. Mary Atieno" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Role &amp; Location *</label>
                <input type="text" name="customer_role" required placeholder="e.g. Restaurant Owner, Busia" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Rating</label>
                <select name="rating" class="form-control" style="background:#fff;">
                    <option value="5" selected>5 Stars (★★★★★)</option>
                    <option value="4">4 Stars (★★★★)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label">Review Text (Facebook / Eggs) *</label>
                <textarea name="content" rows="4" required class="form-control" placeholder="Paste the feedback or egg testimonial..."></textarea>
            </div>
            <label style="display:flex;align-items:center;gap:6px;margin-bottom:16px;cursor:pointer;">
                <input type="checkbox" name="is_approved" value="1" checked>
                <span style="font-weight:600;font-size:0.88rem;">Approved &amp; Visible on Homepage</span>
            </label>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                <i data-lucide="plus" style="width:16px;height:16px;"></i> Add Testimonial
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
include __DIR__ . '/includes/admin_footer.php';
?>
