<?php
/**
 * Website CMS & Content Manager — Busia Chicken Limited
 *
 * Every string and image on the public site lives in `cms_content` and is
 * edited here. The forms are generated from Backend/config/cms_defaults.php,
 * so the admin panel and the templates can never drift apart: add a field to
 * the manifest and it appears here (and on the site) immediately.
 *
 * Tabs: Hero Sliders · Brand & Banner · Homepage · About · Services ·
 *       Products · Shop · Contact · FAQ Page · FAQ Items · Testimonials
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
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager','stock_manager','sales_staff'], true)) {
    echo '<div style="padding:40px;text-align:center;"><h2>Access Denied</h2><p>Only administrators and farm managers can edit website content.</p></div>';
    include __DIR__ . '/includes/admin_footer.php';
    exit;
}

$pdo = function_exists('getDB') ? getDB() : (function_exists('getDatabaseConnection') ? getDatabaseConnection() : null);

if (!$pdo) {
    echo '<div class="admin-card" style="padding:24px;"><h3>Database unavailable</h3><p>Could not connect to the database. Check <code>Backend/config/database.local.php</code>.</p></div>';
    include __DIR__ . '/includes/admin_footer.php';
    exit;
}

// Self-heal: schema + CMS tables must exist before reading or writing.
require_once __DIR__ . '/../../Backend/config/migrations.php';
require_once __DIR__ . '/../../Backend/config/cms.php';
try {
    busiaMigrate($pdo);
    cms_boot($pdo);
} catch (Throwable $e) {
    busiaDbLog('CMS bootstrap failed: ' . $e->getMessage());
}

$message = '';
$error = '';
$tab = (string)($_GET['tab'] ?? 'sliders');

// ── Uploads folder for slider photos ────────────────────────────
$uploadDir = __DIR__ . '/../images/uploads/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

/**
 * cms_content page keys, in menu order.
 */
$cmsPages = ['global', 'home', 'about', 'services', 'products', 'shop', 'contact', 'faq'];

try {
    // ═══════════════ POST HANDLERS ═══════════════
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $action = (string)($_POST['_action'] ?? '');

        // ── 1. Any generated CMS page ──────────────────────────
        if ($action === 'save_page') {
            $pageKey = (string)($_POST['page_key'] ?? '');
            if (!in_array($pageKey, $cmsPages, true)) {
                throw new RuntimeException('Unknown CMS page.');
            }

            $submitted = is_array($_POST['val'] ?? null) ? $_POST['val'] : [];
            $values = [];

            foreach (cms_fields($pageKey) as $key => $field) {
                [$label, $type, $default] = cms_normalise_field($field);

                if ($type === 'image') {
                    $uploaded = cms_upload_image('img_' . $key, $uploadError);
                    if ($uploadError) {
                        $error = $uploadError;
                    }
                    $values[$key] = $uploaded ?? (string)($submitted[$key] ?? '');
                } else {
                    $values[$key] = (string)($submitted[$key] ?? '');
                }
            }

            $count = cms_save_page($pdo, $pageKey, $values);
            if (function_exists('logActivity')) {
                logActivity($pdo, 'update', 'settings', 'CMS updated: ' . cms_page_label($pageKey));
            }
            if (!$error) {
                $message = $count . ' fields updated on ' . cms_page_label($pageKey) . '.';
            }
            $tab = $pageKey;
        }

        // ── 2. Brand, banner & contact (site_settings) ─────────
        if ($action === 'save_content') {
            $settingsToSave = [
                'farm_name'        => trim((string)($_POST['farm_name'] ?? 'Busia Chicken Limited')),
                'farm_slogan'      => trim((string)($_POST['farm_slogan'] ?? 'The Chicken People')),
                'farm_phone'       => trim((string)($_POST['farm_phone'] ?? '')),
                'farm_email'       => trim((string)($_POST['farm_email'] ?? '')),
                'farm_whatsapp'    => trim((string)($_POST['farm_whatsapp'] ?? '')),
                'announcement_bar' => trim((string)($_POST['announcement_bar'] ?? '')),
                'show_announcement'=> isset($_POST['show_announcement']) ? '1' : '0',
                'location_nasira'  => trim((string)($_POST['location_nasira'] ?? '')),
                'hours_nasira'     => trim((string)($_POST['hours_nasira'] ?? '')),
                'google_maps_nasira' => trim((string)($_POST['google_maps_nasira'] ?? '')),
                'location_tangakona' => trim((string)($_POST['location_tangakona'] ?? '')),
                'hours_tangakona'  => trim((string)($_POST['hours_tangakona'] ?? '')),
                'google_maps_tangakona' => trim((string)($_POST['google_maps_tangakona'] ?? '')),
            ];
            siteSettingSaveAll($pdo, $settingsToSave, 'cms');
            if (function_exists('logActivity')) {
                logActivity($pdo, 'update', 'settings', 'Brand, banner and locations updated');
            }
            $message = 'Website brand, banner and locations updated successfully!';
            $tab = 'brand';
        }

        // ── 3. Hero slides ─────────────────────────────────────
        if ($action === 'save_slide') {
            $slideId = (int)($_POST['slide_id'] ?? 0);
            $fields = [
                'slide_order' => (int)($_POST['slide_order'] ?? 1),
                'badge_text' => trim((string)($_POST['badge_text'] ?? '')),
                'title_main' => trim((string)($_POST['title_main'] ?? '')),
                'title_em' => trim((string)($_POST['title_em'] ?? '')),
                'subtitle' => trim((string)($_POST['subtitle'] ?? '')),
                'cta_primary_text' => trim((string)($_POST['cta_primary_text'] ?? 'Explore Products')),
                'cta_primary_link' => trim((string)($_POST['cta_primary_link'] ?? '/Frontend/pages/shop.php')),
                'cta_secondary_text' => trim((string)($_POST['cta_secondary_text'] ?? 'Our Story')),
                'cta_secondary_link' => trim((string)($_POST['cta_secondary_link'] ?? '/Frontend/pages/about.php')),
                'stat1_val' => trim((string)($_POST['stat1_val'] ?? '')),
                'stat1_suffix' => trim((string)($_POST['stat1_suffix'] ?? '')),
                'stat1_label' => trim((string)($_POST['stat1_label'] ?? '')),
                'stat2_val' => trim((string)($_POST['stat2_val'] ?? '')),
                'stat2_suffix' => trim((string)($_POST['stat2_suffix'] ?? '')),
                'stat2_label' => trim((string)($_POST['stat2_label'] ?? '')),
                'stat3_val' => trim((string)($_POST['stat3_val'] ?? '')),
                'stat3_suffix' => trim((string)($_POST['stat3_suffix'] ?? '')),
                'stat3_label' => trim((string)($_POST['stat3_label'] ?? '')),
                'image_url' => trim((string)($_POST['image_url'] ?? '/Frontend/images/adbg.webp')),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            // Optional photo upload overrides the chosen path
            $uploaded = cms_upload_image('slide_file', $slideUploadError);
            if ($slideUploadError) {
                $error = $slideUploadError;
            } elseif ($uploaded) {
                $fields['image_url'] = $uploaded;
            }

            if (!$error) {
                if ($slideId > 0) {
                    $sets = implode(', ', array_map(static fn($c) => "`$c` = ?", array_keys($fields)));
                    $stmt = $pdo->prepare("UPDATE website_sliders SET $sets WHERE id = ?");
                    $stmt->execute([...array_values($fields), $slideId]);
                    $message = 'Hero slide updated successfully!';
                } else {
                    $cols = implode(', ', array_map(static fn($c) => "`$c`", array_keys($fields)));
                    $ph = implode(', ', array_fill(0, count($fields), '?'));
                    $stmt = $pdo->prepare("INSERT INTO website_sliders ($cols) VALUES ($ph)");
                    $stmt->execute(array_values($fields));
                    $message = 'New hero slide added successfully!';
                }
            }
            $tab = 'sliders';
        }

        if ($action === 'delete_slide') {
            $pdo->prepare('DELETE FROM website_sliders WHERE id = ?')->execute([(int)($_POST['slide_id'] ?? 0)]);
            $message = 'Hero slide deleted successfully.';
            $tab = 'sliders';
        }

        if ($action === 'toggle_slide') {
            $pdo->prepare('UPDATE website_sliders SET is_active = NOT is_active WHERE id = ?')->execute([(int)($_POST['slide_id'] ?? 0)]);
            $message = 'Slide visibility toggled.';
            $tab = 'sliders';
        }

        // ── 4. Testimonials ────────────────────────────────────
        if ($action === 'save_testimonial') {
            $cName = trim((string)($_POST['customer_name'] ?? ''));
            $cContent = trim((string)($_POST['content'] ?? ''));
            if ($cName === '' || $cContent === '') {
                throw new RuntimeException('Please provide a customer name and the review text.');
            }
            $stmt = $pdo->prepare('INSERT INTO testimonials (customer_name, customer_role, rating, content, is_approved) VALUES (?,?,?,?,?)');
            $stmt->execute([
                $cName,
                trim((string)($_POST['customer_role'] ?? 'Farmer')),
                (int)($_POST['rating'] ?? 5),
                $cContent,
                isset($_POST['is_approved']) ? 1 : 0,
            ]);
            $message = 'Testimonial added successfully!';
            $tab = 'testimonials';
        }

        if ($action === 'delete_testimonial') {
            $pdo->prepare('DELETE FROM testimonials WHERE id = ?')->execute([(int)($_POST['testimonial_id'] ?? 0)]);
            $message = 'Testimonial deleted.';
            $tab = 'testimonials';
        }

        if ($action === 'toggle_testimonial') {
            $pdo->prepare('UPDATE testimonials SET is_approved = NOT is_approved WHERE id = ?')->execute([(int)($_POST['testimonial_id'] ?? 0)]);
            $message = 'Testimonial visibility toggled.';
            $tab = 'testimonials';
        }

        // ── 5. FAQ items ───────────────────────────────────────
        if ($action === 'save_faq') {
            $question = trim((string)($_POST['question'] ?? ''));
            $answer = trim((string)($_POST['answer'] ?? ''));
            if ($question === '' || $answer === '') {
                throw new RuntimeException('A FAQ item needs both a question and an answer.');
            }
            $faqId = (int)($_POST['faq_id'] ?? 0);
            $category = trim((string)($_POST['category'] ?? 'General'));
            $order = (int)($_POST['sort_order'] ?? 0);
            if ($faqId > 0) {
                $pdo->prepare('UPDATE cms_faq_items SET category = ?, question = ?, answer = ?, sort_order = ?, is_active = ? WHERE id = ?')
                    ->execute([$category, $question, $answer, $order, isset($_POST['is_active']) ? 1 : 0, $faqId]);
                $message = 'FAQ item updated.';
            } else {
                $pdo->prepare('INSERT INTO cms_faq_items (category, question, answer, sort_order, is_active) VALUES (?,?,?,?,?)')
                    ->execute([$category, $question, $answer, $order, isset($_POST['is_active']) ? 1 : 0]);
                $message = 'FAQ item added.';
            }
            $tab = 'faq-items';
        }

        if ($action === 'delete_faq') {
            $pdo->prepare('DELETE FROM cms_faq_items WHERE id = ?')->execute([(int)($_POST['faq_id'] ?? 0)]);
            $message = 'FAQ item deleted.';
            $tab = 'faq-items';
        }

        if ($action === 'toggle_faq') {
            $pdo->prepare('UPDATE cms_faq_items SET is_active = NOT is_active WHERE id = ?')->execute([(int)($_POST['faq_id'] ?? 0)]);
            $message = 'FAQ item visibility toggled.';
            $tab = 'faq-items';
        }
    }
} catch (Throwable $e) {
    busiaDbLog('CMS action failed: ' . $e->getMessage());
    $error = 'Could not save: ' . $e->getMessage();
}

// ═══════════════ DATA FOR RENDER ═══════════════
$currentSettings = [];
try {
    $currentSettings = $pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Throwable $e) {
}

$allSlides = [];
$slideEdit = null;
try {
    $allSlides = $pdo->query('SELECT * FROM website_sliders ORDER BY slide_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
    $editId = (int)($_GET['edit_slide'] ?? 0);
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM website_sliders WHERE id = ?');
        $stmt->execute([$editId]);
        $slideEdit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
} catch (Throwable $e) {
}

$allTestimonials = [];
try {
    $allTestimonials = $pdo->query('SELECT * FROM testimonials ORDER BY id DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

$allFaq = [];
try {
    $allFaq = $pdo->query('SELECT * FROM cms_faq_items ORDER BY category ASC, sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

$cmsValues = cms_map();
$editFaqId = (int)($_GET['edit_faq'] ?? 0);
$faqEdit = null;
if ($editFaqId > 0) {
    foreach ($allFaq as $item) {
        if ((int)$item['id'] === $editFaqId) {
            $faqEdit = $item;
            break;
        }
    }
}

$pageImages = [
    'about'    => ['/Frontend/images/download-2.webp', '/Frontend/images/download-5.webp', '/Frontend/images/farm-exterior.webp'],
    'services' => ['/Frontend/images/adbg.webp', '/Frontend/images/poultry-feed.webp', '/Frontend/images/farm-fresh-eggs.webp'],
    'products' => ['/Frontend/images/broiler-chickens.webp', '/Frontend/images/farm-fresh-eggs.webp', '/Frontend/images/download-8.webp'],
    'shop'     => ['/Frontend/images/adbg.webp', '/Frontend/images/day-old-chicks.webp'],
    'contact'  => ['/Frontend/images/adbg.webp', '/Frontend/images/farm-exterior.webp'],
    'faq'      => ['/Frontend/images/adbg.webp', '/Frontend/images/adbg.webp'],
    'home'     => ['/Frontend/images/adbg.webp', '/Frontend/images/farm-fresh-eggs.webp'],
    'global'   => ['/Frontend/images/logo-sm.webp', '/Frontend/images/logo.svg'],
];

$tabs = [
    'sliders'      => ['Hero Sliders', 'image'],
    'brand'        => ['Brand & Banner', 'map-pin'],
    'global'       => ['Header & Footer', 'panel-top'],
    'home'         => ['Homepage', 'home'],
    'about'        => ['About', 'info'],
    'services'     => ['Services', 'sparkles'],
    'products'     => ['Products', 'package'],
    'shop'         => ['Shop', 'shopping-bag'],
    'contact'      => ['Contact', 'map-pin'],
    'faq'          => ['FAQ Page', 'help-circle'],
    'faq-items'    => ['FAQ Items', 'list'],
    'testimonials' => ['Reviews', 'message-square'],
];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:14px;">
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
            <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;">Website CMS &amp; Content Manager</h1>
            <span style="background:#dcfce7;color:#166534;font-size:0.75rem;font-weight:700;padding:3px 9px;border-radius:4px;text-transform:uppercase;">Live Frontend</span>
        </div>
        <p style="margin:0;color:#64748b;font-size:0.92rem;">Every heading, paragraph, image and link on the public site — editable here, no code required.</p>
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

<!-- Tabs -->
<div style="display:flex;gap:6px;background:#f1f5f9;padding:6px;border-radius:10px;margin-bottom:24px;overflow-x:auto;">
    <?php foreach ($tabs as $key => [$label, $icon]): ?>
    <a href="?tab=<?php echo urlencode($key); ?>" style="display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.86rem;white-space:nowrap;<?php echo $tab === $key ? 'background:#fff;color:var(--admin-primary);box-shadow:0 1px 4px rgba(0,0,0,0.06);' : 'color:#64748b;'; ?>">
        <i data-lucide="<?php echo $icon; ?>" style="width:15px;height:15px;"></i> <?php echo htmlspecialchars($label, ENT_QUOTES); ?>
    </a>
    <?php endforeach; ?>
</div>

<?php // ══════════ HERO SLIDERS ══════════ ?>
<?php if ($tab === 'sliders'): ?>
<div style="display:grid;grid-template-columns:1fr 420px;gap:24px;align-items:start;">
    <div class="admin-card" style="padding:22px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.15rem;">Active Hero Slides (<?php echo count($allSlides); ?>)</h3>
            <a href="?tab=sliders" class="btn btn-outline btn-sm"><i data-lucide="plus" style="width:14px;height:14px;"></i> New Slide</a>
        </div>

        <?php if (!$allSlides): ?>
            <p style="color:#64748b;text-align:center;padding:30px;">No slides yet. Create one on the right.</p>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:14px;">
            <?php foreach ($allSlides as $s): ?>
            <div style="display:flex;gap:16px;align-items:center;padding:14px;border:1px solid var(--admin-border);border-radius:10px;background:<?php echo $slideEdit && (int)$slideEdit['id'] === (int)$s['id'] ? '#f0fdf4' : '#fff'; ?>;">
                <div style="width:110px;height:75px;border-radius:6px;overflow:hidden;flex-shrink:0;position:relative;background:#000;">
                    <img src="<?php echo htmlspecialchars((string)$s['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:100%;height:100%;object-fit:cover;opacity:0.85;">
                    <span style="position:absolute;top:4px;left:4px;background:rgba(0,0,0,0.7);color:#fff;font-size:0.65rem;font-weight:800;padding:2px 6px;border-radius:3px;">#<?php echo (int)$s['slide_order']; ?></span>
                </div>
                <div style="flex-grow:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;">
                        <span style="font-size:0.75rem;font-weight:700;color:var(--admin-primary);text-transform:uppercase;"><?php echo htmlspecialchars((string)$s['badge_text'], ENT_QUOTES); ?></span>
                        <span style="font-size:0.7rem;padding:1px 6px;border-radius:3px;<?php echo $s['is_active'] ? 'background:#dcfce7;color:#166534;' : 'background:#fee2e2;color:#991b1b;'; ?>"><?php echo $s['is_active'] ? 'Active' : 'Hidden'; ?></span>
                    </div>
                    <strong style="font-size:0.95rem;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($s['title_main'] . ' ' . $s['title_em'], ENT_QUOTES); ?></strong>
                    <p style="margin:2px 0 0;font-size:0.82rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars((string)$s['subtitle'], ENT_QUOTES); ?></p>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="_action" value="toggle_slide">
                        <input type="hidden" name="slide_id" value="<?php echo (int)$s['id']; ?>">
                        <button type="submit" class="btn btn-outline btn-sm" title="Show / hide" style="padding:6px 9px;"><i data-lucide="<?php echo $s['is_active'] ? 'eye' : 'eye-off'; ?>" style="width:14px;height:14px;"></i></button>
                    </form>
                    <a href="?tab=sliders&edit_slide=<?php echo (int)$s['id']; ?>" class="btn btn-outline btn-sm" style="padding:6px 10px;"><i data-lucide="edit-2" style="width:14px;height:14px;"></i></a>
                    <form method="POST" style="margin:0;" onsubmit="return confirm('Delete this slide?');">
                        <input type="hidden" name="_action" value="delete_slide">
                        <input type="hidden" name="slide_id" value="<?php echo (int)$s['id']; ?>">
                        <button type="submit" class="btn btn-outline btn-sm" style="padding:6px 9px;color:#dc2626;"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="admin-card" style="padding:22px;">
        <h3 style="margin:0 0 16px;font-family:'Outfit',sans-serif;font-size:1.15rem;"><?php echo $slideEdit ? 'Edit Slide #' . (int)$slideEdit['slide_order'] : 'Create Hero Slide'; ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_action" value="save_slide">
            <input type="hidden" name="slide_id" value="<?php echo (int)($slideEdit['id'] ?? 0); ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label class="form-label">Order</label>
                    <input type="number" name="slide_order" min="1" max="99" value="<?php echo (int)($slideEdit['slide_order'] ?? 1); ?>" class="form-control">
                </div>
                <div>
                    <label class="form-label">Visibility</label>
                    <label style="display:flex;align-items:center;gap:8px;padding-top:8px;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" <?php echo (!$slideEdit || !empty($slideEdit['is_active'])) ? 'checked' : ''; ?>> Show on website
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Badge text</label>
                <input type="text" name="badge_text" value="<?php echo htmlspecialchars((string)($slideEdit['badge_text'] ?? 'Welcome to Busia Chicken Farm'), ENT_QUOTES); ?>" class="form-control">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label class="form-label">Main heading</label>
                    <input type="text" name="title_main" value="<?php echo htmlspecialchars((string)($slideEdit['title_main'] ?? 'Quality Poultry for'), ENT_QUOTES); ?>" class="form-control">
                </div>
                <div>
                    <label class="form-label">Gold accent</label>
                    <input type="text" name="title_em" value="<?php echo htmlspecialchars((string)($slideEdit['title_em'] ?? 'East Africa'), ENT_QUOTES); ?>" class="form-control">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Subtitle</label>
                <textarea name="subtitle" rows="3" class="form-control" style="font-size:0.86rem;"><?php echo htmlspecialchars((string)($slideEdit['subtitle'] ?? 'Premium broilers, layers and layer feeds — raised with care, delivered with confidence.'), ENT_QUOTES); ?></textarea>
            </div>

            <div style="padding:14px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;margin-bottom:14px;">
                <label class="form-label" style="display:flex;align-items:center;gap:6px;"><i data-lucide="upload" style="width:16px;height:16px;"></i> Slide photo</label>
                <input type="file" name="slide_file" accept="image/*" class="form-control" style="padding:6px;background:#fff;margin-bottom:8px;">
                <input type="text" name="image_url" value="<?php echo htmlspecialchars((string)($slideEdit['image_url'] ?? '/Frontend/images/adbg.webp'), ENT_QUOTES); ?>" class="form-control" style="background:#fff;" placeholder="/Frontend/images/adbg.webp">
                <small style="display:block;color:#64748b;margin-top:6px;">Upload a new photo, or paste an existing image path.</small>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label class="form-label">Primary button text</label>
                    <input type="text" name="cta_primary_text" value="<?php echo htmlspecialchars((string)($slideEdit['cta_primary_text'] ?? 'Explore Products'), ENT_QUOTES); ?>" class="form-control">
                </div>
                <div>
                    <label class="form-label">Primary button link</label>
                    <input type="text" name="cta_primary_link" value="<?php echo htmlspecialchars((string)($slideEdit['cta_primary_link'] ?? '/Frontend/pages/shop.php'), ENT_QUOTES); ?>" class="form-control">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                <div>
                    <label class="form-label">Secondary button text</label>
                    <input type="text" name="cta_secondary_text" value="<?php echo htmlspecialchars((string)($slideEdit['cta_secondary_text'] ?? 'Our Story'), ENT_QUOTES); ?>" class="form-control">
                </div>
                <div>
                    <label class="form-label">Secondary button link</label>
                    <input type="text" name="cta_secondary_link" value="<?php echo htmlspecialchars((string)($slideEdit['cta_secondary_link'] ?? '/Frontend/pages/about.php'), ENT_QUOTES); ?>" class="form-control">
                </div>
            </div>

            <label class="form-label" style="font-weight:700;margin-bottom:8px;display:block;">Three stat metrics</label>
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:8px;margin-bottom:8px;">
                <input type="text" name="stat<?php echo $i; ?>_val" placeholder="Value" value="<?php echo htmlspecialchars((string)($slideEdit["stat{$i}_val"] ?? ''), ENT_QUOTES); ?>" class="form-control">
                <input type="text" name="stat<?php echo $i; ?>_suffix" placeholder="Suffix" value="<?php echo htmlspecialchars((string)($slideEdit["stat{$i}_suffix"] ?? ''), ENT_QUOTES); ?>" class="form-control">
                <input type="text" name="stat<?php echo $i; ?>_label" placeholder="Label" value="<?php echo htmlspecialchars((string)($slideEdit["stat{$i}_label"] ?? ''), ENT_QUOTES); ?>" class="form-control">
            </div>
            <?php endfor; ?>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;margin-top:8px;">
                <i data-lucide="check" style="width:18px;height:18px;"></i> <?php echo $slideEdit ? 'Update Slide' : 'Save & Publish Slide'; ?>
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php // ══════════ BRAND, BANNER & LOCATIONS (site_settings) ══════════ ?>
<?php if ($tab === 'brand'): ?>
<div class="admin-card" style="padding:26px;max-width:900px;">
    <h3 style="margin:0 0 8px;font-family:'Outfit',sans-serif;font-size:1.2rem;">Brand, Announcement &amp; Business Locations</h3>
    <p style="color:#64748b;font-size:0.9rem;margin:0 0 24px;">These values are used across the whole website (header, footer, contact details, WhatsApp links).</p>

    <form method="POST">
        <input type="hidden" name="_action" value="save_content">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div>
                <label class="form-label">Company name</label>
                <input type="text" name="farm_name" value="<?php echo htmlspecialchars((string)($currentSettings['farm_name'] ?? 'Busia Chicken Limited'), ENT_QUOTES); ?>" class="form-control">
            </div>
            <div>
                <label class="form-label">Official slogan</label>
                <input type="text" name="farm_slogan" value="<?php echo htmlspecialchars((string)($currentSettings['farm_slogan'] ?? 'The Chicken People'), ENT_QUOTES); ?>" class="form-control">
            </div>
        </div>

        <div style="padding:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;margin-bottom:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <strong style="color:#166534;font-size:0.95rem;">Top announcement banner</strong>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                    <input type="checkbox" name="show_announcement" value="1" <?php echo ((string)($currentSettings['show_announcement'] ?? '1')) === '1' ? 'checked' : ''; ?>>
                    <span style="font-size:0.85rem;font-weight:700;color:#166534;">Enable banner</span>
                </label>
            </div>
            <input type="text" name="announcement_bar" value="<?php echo htmlspecialchars((string)($currentSettings['announcement_bar'] ?? ''), ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
            <small style="color:#15803d;display:block;margin-top:6px;">Shown at the very top of every public page.</small>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:24px;">
            <div>
                <label class="form-label">Phone hotline</label>
                <input type="text" name="farm_phone" value="<?php echo htmlspecialchars((string)($currentSettings['farm_phone'] ?? '+254 727 585 599'), ENT_QUOTES); ?>" class="form-control">
            </div>
            <div>
                <label class="form-label">WhatsApp number</label>
                <input type="text" name="farm_whatsapp" value="<?php echo htmlspecialchars((string)($currentSettings['farm_whatsapp'] ?? '+254727585599'), ENT_QUOTES); ?>" class="form-control">
            </div>
            <div>
                <label class="form-label">Email address</label>
                <input type="email" name="farm_email" value="<?php echo htmlspecialchars((string)($currentSettings['farm_email'] ?? 'info@busiachicken.com'), ENT_QUOTES); ?>" class="form-control">
            </div>
        </div>

        <?php
        $locations = [
            ['nasira', 'Location 1: Nasira AC Sub-location', 'Nasira AC Sub-location, Busibwabo Location, Matayos Sub-County, Busia, Kenya', 'Mon - Sat: 7:00 AM - 6:00 PM | Sun: 8:00 AM - 1:00 PM', 'https://www.google.com/maps/place/0%C2%B026\'06.0%22N+34%C2%B012\'07.6%22E/@0.4349969,34.1995236,17z/data=!3m1!4b1!4m4!3m3!8m2!3d0.4349969!4d34.2020985?hl=en&entry=ttu&g_ep=EgoyMDI2MTExNi4wIKXMDSoASAFQAw%3D%3D'],
            ['tangakona', 'Location 2: Tangakona Farm', 'Tangakona Junction / Market along Busia-Kisumu Highway, Busia County, Kenya', 'Mon - Sat: 8:00 AM - 6:30 PM | Sun: Closed', 'https://www.google.com/maps?q=0.468734,34.2188207&z=17&hl=en'],
        ];
        foreach ($locations as [$key, $heading, $addrDefault, $hoursDefault, $mapsDefault]):
        ?>
        <div style="padding:18px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:20px;background:#fafafa;">
            <h4 style="margin:0 0 12px;font-size:1.02rem;"><?php echo htmlspecialchars($heading, ENT_QUOTES); ?></h4>
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Physical address</label>
                <input type="text" name="location_<?php echo $key; ?>" value="<?php echo htmlspecialchars((string)($currentSettings['location_' . $key] ?? $addrDefault), ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label class="form-label">Operating hours</label>
                    <input type="text" name="hours_<?php echo $key; ?>" value="<?php echo htmlspecialchars((string)($currentSettings['hours_' . $key] ?? $hoursDefault), ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
                </div>
                <div>
                    <label class="form-label">Google Maps link</label>
                    <input type="text" name="google_maps_<?php echo $key; ?>" value="<?php echo htmlspecialchars((string)($currentSettings['google_maps_' . $key] ?? $mapsDefault), ENT_QUOTES); ?>" class="form-control" style="background:#fff;">
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary" style="padding:12px 28px;"><i data-lucide="save" style="width:18px;height:18px;"></i> Save Changes</button>
    </form>
</div>
<?php endif; ?>

<?php // ══════════ GENERATED CONTENT PAGES ══════════ ?>
<?php if (in_array($tab, $cmsPages, true)): ?>
<?php
$fields = cms_fields($tab);
$saved = $cmsValues[$tab] ?? [];
?>
<div class="admin-card" style="padding:26px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
        <div>
            <h3 style="margin:0 0 6px;font-family:'Outfit',sans-serif;font-size:1.2rem;"><?php echo htmlspecialchars(cms_page_label($tab), ENT_QUOTES); ?></h3>
            <p style="color:#64748b;font-size:0.9rem;margin:0;"><?php echo count($fields); ?> editable fields. Blank fields fall back to the built-in default.</p>
        </div>
        <a href="/<?php echo $tab === 'global' ? '' : ($tab === 'home' ? '' : 'Frontend/pages/' . $tab . '.php'); ?>" target="_blank" class="btn btn-outline btn-sm">
            <i data-lucide="external-link" style="width:14px;height:14px;"></i> Preview
        </a>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_action" value="save_page">
        <input type="hidden" name="page_key" value="<?php echo htmlspecialchars($tab, ENT_QUOTES); ?>">

        <div style="display:grid;gap:16px;">
        <?php foreach ($fields as $key => $definition): ?>
            <?php
            [$label, $type, $default] = cms_normalise_field($definition);
            $value = array_key_exists($key, $saved) ? $saved[$key] : $default;
            $inputId = 'f_' . $tab . '_' . $key;
            ?>
            <div>
                <label class="form-label" for="<?php echo $inputId; ?>">
                    <?php echo htmlspecialchars($label !== '' ? $label : $key, ENT_QUOTES); ?>
                    <span style="color:#94a3b8;font-weight:500;font-size:0.78rem;">(<?php echo htmlspecialchars($key, ENT_QUOTES); ?>)</span>
                </label>

                <?php if ($type === 'image'): ?>
                    <div style="display:flex;gap:14px;align-items:flex-start;flex-wrap:wrap;padding:12px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;">
                        <img src="<?php echo htmlspecialchars($value !== '' ? $value : $default, ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:130px;height:86px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0;background:#fff;">
                        <div style="flex:1;min-width:240px;">
                            <input type="file" name="img_<?php echo htmlspecialchars($key, ENT_QUOTES); ?>" accept="image/*" class="form-control" style="padding:6px;background:#fff;margin-bottom:8px;">
                            <input type="text" id="<?php echo $inputId; ?>" name="val[<?php echo htmlspecialchars($key, ENT_QUOTES); ?>]" value="<?php echo htmlspecialchars($value, ENT_QUOTES); ?>" class="form-control" style="background:#fff;" placeholder="<?php echo htmlspecialchars($default, ENT_QUOTES); ?>">
                            <small style="color:#64748b;display:block;margin-top:6px;">Upload a new image or paste a path. Leave empty to use the default.</small>
                        </div>
                    </div>
                <?php elseif ($type === 'textarea' || $type === 'html'): ?>
                    <textarea id="<?php echo $inputId; ?>" name="val[<?php echo htmlspecialchars($key, ENT_QUOTES); ?>]" rows="<?php echo $type === 'html' ? 12 : 4; ?>" class="form-control"><?php echo htmlspecialchars($value, ENT_QUOTES); ?></textarea>
                <?php else: ?>
                    <input type="<?php echo $type === 'url' ? 'text' : 'text'; ?>" id="<?php echo $inputId; ?>" name="val[<?php echo htmlspecialchars($key, ENT_QUOTES); ?>]" value="<?php echo htmlspecialchars($value, ENT_QUOTES); ?>" class="form-control">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>

        <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="padding:12px 28px;">
                <i data-lucide="save" style="width:18px;height:18px;"></i> Save <?php echo htmlspecialchars(cms_page_label($tab), ENT_QUOTES); ?>
            </button>
            <a href="/<?php echo $tab === 'home' || $tab === 'global' ? '' : 'Frontend/pages/' . $tab . '.php'; ?>" target="_blank" class="btn btn-outline" style="padding:12px 20px;">View page</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php // ══════════ FAQ ITEMS ══════════ ?>
<?php if ($tab === 'faq-items'): ?>
<div style="display:grid;grid-template-columns:1fr 420px;gap:24px;align-items:start;">
    <div class="admin-card" style="padding:22px;">
        <h3 style="margin:0 0 16px;font-family:'Outfit',sans-serif;font-size:1.15rem;">FAQ Items (<?php echo count($allFaq); ?>)</h3>
        <?php if (!$allFaq): ?>
            <p style="color:#64748b;padding:20px;text-align:center;">No FAQ items yet. Add the first one on the right.</p>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php $lastCategory = null; foreach ($allFaq as $item): ?>
                <?php if ($item['category'] !== $lastCategory): $lastCategory = $item['category']; ?>
                    <h4 style="margin:14px 0 2px;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;"><?php echo htmlspecialchars($lastCategory, ENT_QUOTES); ?></h4>
                <?php endif; ?>
                <div style="padding:12px 14px;border:1px solid var(--admin-border);border-radius:8px;background:<?php echo $item['is_active'] ? '#fff' : '#f8fafc'; ?>;">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div style="min-width:0;">
                            <strong style="display:block;font-size:0.92rem;"><?php echo htmlspecialchars($item['question'], ENT_QUOTES); ?></strong>
                            <p style="margin:4px 0 0;font-size:0.84rem;color:#64748b;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?php echo htmlspecialchars($item['answer'], ENT_QUOTES); ?></p>
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <a href="?tab=faq-items&edit_faq=<?php echo (int)$item['id']; ?>" class="btn btn-outline btn-sm" style="padding:5px 9px;"><i data-lucide="edit-2" style="width:13px;height:13px;"></i></a>
                            <form method="POST" style="margin:0;"><input type="hidden" name="_action" value="toggle_faq"><input type="hidden" name="faq_id" value="<?php echo (int)$item['id']; ?>"><button type="submit" class="btn btn-outline btn-sm" style="padding:5px 9px;"><i data-lucide="<?php echo $item['is_active'] ? 'eye' : 'eye-off'; ?>" style="width:13px;height:13px;"></i></button></form>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('Delete this FAQ item?');"><input type="hidden" name="_action" value="delete_faq"><input type="hidden" name="faq_id" value="<?php echo (int)$item['id']; ?>"><button type="submit" class="btn btn-outline btn-sm" style="padding:5px 9px;color:#dc2626;"><i data-lucide="trash-2" style="width:13px;height:13px;"></i></button></form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="admin-card" style="padding:22px;">
        <h3 style="margin:0 0 16px;font-family:'Outfit',sans-serif;font-size:1.15rem;"><?php echo $faqEdit ? 'Edit FAQ Item' : 'Add FAQ Item'; ?></h3>
        <form method="POST">
            <input type="hidden" name="_action" value="save_faq">
            <input type="hidden" name="faq_id" value="<?php echo (int)($faqEdit['id'] ?? 0); ?>">
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Category</label>
                <input type="text" name="category" value="<?php echo htmlspecialchars((string)($faqEdit['category'] ?? 'Products & Chickens'), ENT_QUOTES); ?>" class="form-control" placeholder="e.g. Ordering & Delivery">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Question</label>
                <input type="text" name="question" required value="<?php echo htmlspecialchars((string)($faqEdit['question'] ?? ''), ENT_QUOTES); ?>" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Answer</label>
                <textarea name="answer" rows="5" required class="form-control"><?php echo htmlspecialchars((string)($faqEdit['answer'] ?? ''), ENT_QUOTES); ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label">Sort order</label>
                <input type="number" name="sort_order" value="<?php echo (int)($faqEdit['sort_order'] ?? (count($allFaq) + 1)); ?>" class="form-control">
            </div>
            <label style="display:flex;align-items:center;gap:6px;margin-bottom:16px;cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" <?php echo (!$faqEdit || !empty($faqEdit['is_active'])) ? 'checked' : ''; ?>> Show on website
            </label>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                <i data-lucide="check" style="width:16px;height:16px;"></i> <?php echo $faqEdit ? 'Update FAQ' : 'Add FAQ'; ?>
            </button>
            <?php if ($faqEdit): ?>
                <a href="?tab=faq-items" class="btn btn-outline" style="width:100%;justify-content:center;margin-top:10px;">Cancel edit</a>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php endif; ?>

<?php // ══════════ TESTIMONIALS ══════════ ?>
<?php if ($tab === 'testimonials'): ?>
<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">
    <div class="admin-card" style="padding:22px;">
        <h3 style="margin:0 0 16px;font-family:'Outfit',sans-serif;font-size:1.15rem;">Customer Reviews &amp; Testimonials (<?php echo count($allTestimonials); ?>)</h3>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($allTestimonials as $t): ?>
            <div style="padding:14px;border:1px solid #e2e8f0;border-radius:8px;background:<?php echo $t['is_approved'] ? '#fff' : '#f8fafc'; ?>;">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:6px;">
                    <div style="min-width:0;">
                        <strong style="font-size:0.95rem;"><?php echo htmlspecialchars($t['customer_name'], ENT_QUOTES); ?></strong>
                        <span style="color:#64748b;font-size:0.8rem;margin-left:6px;">(<?php echo htmlspecialchars((string)($t['customer_role'] ?? 'Farmer'), ENT_QUOTES); ?>)</span>
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0;align-items:center;">
                        <span style="color:#eab308;font-size:0.85rem;"><?php echo str_repeat('★', max(1, min(5, (int)$t['rating']))); ?></span>
                        <form method="POST" style="margin:0;"><input type="hidden" name="_action" value="toggle_testimonial"><input type="hidden" name="testimonial_id" value="<?php echo (int)$t['id']; ?>"><button type="submit" class="btn btn-outline btn-sm" style="padding:4px 8px;"><i data-lucide="<?php echo $t['is_approved'] ? 'eye' : 'eye-off'; ?>" style="width:12px;height:12px;"></i></button></form>
                        <form method="POST" style="margin:0;" onsubmit="return confirm('Delete this review?');"><input type="hidden" name="_action" value="delete_testimonial"><input type="hidden" name="testimonial_id" value="<?php echo (int)$t['id']; ?>"><button type="submit" class="btn btn-outline btn-sm" style="padding:4px 8px;color:#dc2626;"><i data-lucide="trash-2" style="width:12px;height:12px;"></i></button></form>
                    </div>
                </div>
                <p style="margin:0;color:#334155;font-size:0.88rem;line-height:1.5;">&ldquo;<?php echo htmlspecialchars($t['content'], ENT_QUOTES); ?>&rdquo;</p>
            </div>
            <?php endforeach; ?>
            <?php if (!$allTestimonials): ?>
                <p style="color:#64748b;text-align:center;padding:24px;">No reviews yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-card" style="padding:22px;">
        <h3 style="margin:0 0 16px;font-family:'Outfit',sans-serif;font-size:1.15rem;">Add Review</h3>
        <form method="POST">
            <input type="hidden" name="_action" value="save_testimonial">
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Customer / business name</label>
                <input type="text" name="customer_name" required class="form-control" placeholder="e.g. Mary Atieno">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Role &amp; location</label>
                <input type="text" name="customer_role" class="form-control" placeholder="e.g. Restaurant Owner, Busia">
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label">Rating</label>
                <select name="rating" class="form-control" style="background:#fff;">
                    <?php for ($r = 5; $r >= 1; $r--): ?>
                        <option value="<?php echo $r; ?>"><?php echo str_repeat('★', $r); ?> (<?php echo $r; ?>)</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label">Review text</label>
                <textarea name="content" rows="4" required class="form-control"></textarea>
            </div>
            <label style="display:flex;align-items:center;gap:6px;margin-bottom:16px;cursor:pointer;">
                <input type="checkbox" name="is_approved" value="1" checked> Approved &amp; visible on the homepage
            </label>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                <i data-lucide="plus" style="width:16px;height:16px;"></i> Add Testimonial
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
