<?php
/**
 * Website content repository (CMS).
 *
 * Every editable string and image on the public site is stored in
 * `cms_content`, grouped by page (`home`, `about`, `services`,
 * `products`, `shop`, `contact`, `faq`, `global`) and read through
 * cms(). Templates never hard-code copy: they call cms() with the key
 * and a last-resort fallback.
 *
 * The canonical list of fields (labels, types, default values) lives
 * in cms_defaults.php. That manifest drives both the admin forms and
 * the seeding, so code and database can never drift: adding a field
 * to the manifest makes it appear in the admin panel and on the site.
 */
declare(strict_types=1);

require_once __DIR__ . '/migrations.php';

/**
 * The field manifest, loaded once per request.
 */
function cms_manifest(): array
{
    static $manifest = null;
    if ($manifest === null) {
        $file = __DIR__ . '/cms_defaults.php';
        $manifest = is_file($file) ? (array)require $file : [];
    }
    return $manifest;
}

/**
 * Fields for one page, in display order.
 */
function cms_fields(string $page): array
{
    $manifest = cms_manifest();
    return $manifest[$page]['fields'] ?? [];
}

/**
 * Human label for a page key.
 */
function cms_page_label(string $page): string
{
    $manifest = cms_manifest();
    return $manifest[$page]['label'] ?? ucfirst($page);
}

/**
 * Create the CMS tables when missing (idempotent, two cheap queries).
 */
function cms_table_ready(?PDO $pdo = null): bool
{
    if ($pdo === null) {
        $pdo = cms_pdo();
    }
    if (!$pdo) {
        return false;
    }
    if (tableExists($pdo, 'cms_content') && tableExists($pdo, 'cms_faq_items')) {
        return true;
    }
    return cms_boot($pdo)['ok'];
}

/**
 * Resolve a PDO connection without assuming a specific bootstrap order.
 */
function cms_pdo(): ?PDO
{
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }
    if (function_exists('getDB')) {
        $pdo = getDB();
        return $pdo instanceof PDO ? $pdo : null;
    }
    if (function_exists('getDatabaseConnection')) {
        $pdo = getDatabaseConnection();
        return $pdo instanceof PDO ? $pdo : null;
    }
    return null;
}

/**
 * Create the tables and seed every manifest field that has no row yet.
 * Never overwrites an administrator's saved value.
 *
 * @return array{ok:bool,created:int}
 */
function cms_boot(?PDO $pdo = null): array
{
    static $done = false;
    $result = ['ok' => $done, 'created' => 0];
    if ($done) {
        return $result;
    }

    if ($pdo === null) {
        $pdo = cms_pdo();
    }
    if (!$pdo) {
        return $result;
    }

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS cms_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_key VARCHAR(50) NOT NULL,
            content_key VARCHAR(100) NOT NULL,
            label VARCHAR(150) NOT NULL DEFAULT '',
            content_type ENUM('text','textarea','html','image','url') NOT NULL DEFAULT 'text',
            content_value MEDIUMTEXT NULL,
            default_value MEDIUMTEXT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_page_content (page_key, content_key),
            INDEX idx_cms_page (page_key, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS cms_faq_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category VARCHAR(80) NOT NULL DEFAULT 'General',
            question VARCHAR(255) NOT NULL,
            answer TEXT NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_faq_category (category, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        busiaDbLog('Could not create CMS tables: ' . $e->getMessage());
        return $result;
    }

    // Seed missing manifest fields.
    $created = 0;
    try {
        $stmt = $pdo->prepare('INSERT IGNORE INTO cms_content
            (page_key, content_key, label, content_type, content_value, default_value, sort_order)
            VALUES (?,?,?,?,?,?,?)');
        foreach (cms_manifest() as $page => $definition) {
            $order = 0;
            foreach (($definition['fields'] ?? []) as $key => $field) {
                [$label, $type, $default] = cms_normalise_field($field);
                $stmt->execute([$page, $key, $label, $type, $default, $default, $order]);
                $created += $stmt->rowCount();
                $order++;
            }
        }
    } catch (Throwable $e) {
        busiaDbLog('CMS seeding failed: ' . $e->getMessage());
    }

    $done = true;
    $result = ['ok' => true, 'created' => $created];
    return $result;
}

/**
 * Manifest entry → [label, type, default].
 */
function cms_normalise_field($field): array
{
    if (is_array($field)) {
        return [
            (string)($field[0] ?? ''),
            (string)($field[1] ?? 'text'),
            (string)($field[2] ?? ''),
        ];
    }
    return ['', 'text', (string)$field];
}

/**
 * Whole content table, cached for the request: ['page']['key'] => value.
 */
function cms_map(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [];

    $pdo = cms_pdo();
    if ($pdo) {
        try {
            $rows = $pdo->query('SELECT page_key, content_key, content_value FROM cms_content')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $value = $row['content_value'];
                if ($value === null) {
                    continue; // fall through to the manifest default
                }
                $map[$row['page_key']][$row['content_key']] = (string)$value;
            }
        } catch (Throwable $e) {
            // Table missing or query failed: manifest defaults still render.
        }
    }
    return $map;
}

/**
 * Read one content field. Priority: saved value → manifest default → fallback.
 */
function cms(string $page, string $key, ?string $fallback = null): string
{
    $map = cms_map();
    if (isset($map[$page][$key])) {
        return $map[$page][$key];
    }
    $fields = cms_fields($page);
    if (isset($fields[$key])) {
        [, , $default] = cms_normalise_field($fields[$key]);
        if ($default !== '') {
            return $default;
        }
    }
    return $fallback ?? '';
}

/**
 * Read one field as escaped plain text (safe to echo).
 */
function cms_text(string $page, string $key, ?string $fallback = null): string
{
    return htmlspecialchars(cms($page, $key, $fallback), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Read one field as escaped HTML where a small set of emphasis tags is
 * allowed, so editors can keep "Farming done <em>right</em>" styling
 * without opening the page to arbitrary markup.
 */
function cms_html(string $page, string $key, ?string $fallback = null): string
{
    $safe = htmlspecialchars(cms($page, $key, $fallback), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    foreach (['em', 'strong', 'b', 'i', 'br'] as $tag) {
        $safe = preg_replace('/&lt;(\/?)' . $tag . '\s*\/?&gt;/i', '<$1' . $tag . '>', $safe) ?? $safe;
    }
    return $safe;
}

/**
 * Read an image field and run it through the site's image optimiser.
 * Returns the fallback untouched when the field is empty.
 */
function cms_img(string $page, string $key, string $fallback = ''): string
{
    $value = trim(cms($page, $key, $fallback));
    if ($value === '') {
        return $fallback;
    }
    return function_exists('optimizedSiteImage') ? optimizedSiteImage($value) : $value;
}

/**
 * Newline- or comma-separated multi-value field → array.
 */
function cms_lines(string $page, string $key, array $fallback = []): array
{
    $raw = cms($page, $key, implode(', ', $fallback));
    if (trim($raw) === '') {
        return $fallback;
    }
    $parts = preg_split('/\r\n|\r|\n/', $raw) ?: [];
    if (count($parts) <= 1) {
        $parts = explode(',', $raw);
    }
    $out = [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part !== '') {
            $out[] = $part;
        }
    }
    return $out ?: $fallback;
}

/**
 * Image used when a catalogue product has no photo of its own.
 * Editable per product type under Admin → Website CMS → Shop Page.
 */
function cms_product_image(string $type, string $fallback = ''): string
{
    $map = [
        'feed'         => 'fallback_feed',
        'eggs'         => 'fallback_eggs',
        'chicks'       => 'fallback_chicks',
        'organic'      => 'fallback_organic',
        'live_chicken' => 'fallback_live_chicken',
    ];
    $key = $map[$type] ?? 'fallback_default';
    $default = [
        'fallback_feed' => '/Frontend/images/download-8.webp',
        'fallback_eggs' => '/Frontend/images/farm-fresh-eggs.webp',
        'fallback_chicks' => '/Frontend/images/day-old-chicks.webp',
        'fallback_organic' => '/Frontend/images/poultry-feed.webp',
        'fallback_live_chicken' => '/Frontend/images/broiler-chickens.webp',
        'fallback_default' => '/Frontend/images/adbg.webp',
    ][$key];
    return cms_img('shop', $key, $fallback !== '' ? $fallback : $default);
}

/**
 * Numbered block helper: cms_rows('home', 'why', 6, ['title','text'])
 * reads why_1_title, why_1_text, why_2_title, ...
 */
function cms_rows(string $page, string $prefix, int $count, array $fields): array
{
    $rows = [];
    for ($i = 1; $i <= $count; $i++) {
        $row = [];
        foreach ($fields as $field) {
            $row[$field] = cms($page, $prefix . '_' . $i . '_' . $field);
        }
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Save a batch of fields for one page. Unknown keys are ignored, so a
 * tampered form cannot inject arbitrary rows.
 *
 * @param array<string,string> $values
 */
function cms_save_page(?PDO $pdo, string $page, array $values): int
{
    if ($pdo === null) {
        $pdo = cms_pdo();
    }
    if (!$pdo) {
        throw new RuntimeException('No database connection.');
    }
    cms_boot($pdo);

    $fields = cms_fields($page);
    if (!$fields) {
        throw new RuntimeException('Unknown CMS page: ' . $page);
    }

    $stmt = $pdo->prepare('INSERT INTO cms_content (page_key, content_key, label, content_type, content_value, default_value, sort_order)
        VALUES (?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE content_value = VALUES(content_value), label = VALUES(label), content_type = VALUES(content_type)');

    $order = 0;
    $saved = 0;
    foreach ($fields as $key => $field) {
        [$label, $type, $default] = cms_normalise_field($field);
        $value = array_key_exists($key, $values) ? (string)$values[$key] : $default;
        $stmt->execute([$page, $key, $label, $type, $value, $default, $order]);
        $saved++;
        $order++;
    }
    return $saved;
}

/**
 * Handle an uploaded CMS image. Returns the stored web path, or null on
 * failure (with $error populated).
 */
function cms_upload_image(string $fieldName, ?string &$error = null): ?string
{
    $error = null;
    if (empty($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed (error code ' . (int)$file['error'] . ').';
        return null;
    }
    if (($file['size'] ?? 0) > 8 * 1024 * 1024) {
        $error = 'Image is larger than 8 MB. Please resize it first.';
        return null;
    }

    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg' => 'jpg', 'jpeg' => 'jpg', 'png' => 'png', 'webp' => 'webp', 'gif' => 'gif', 'svg' => 'svg'];
    if (!isset($allowed[$ext])) {
        $error = 'Unsupported image type. Use JPG, PNG, WEBP, GIF or SVG.';
        return null;
    }

    $uploadDir = dirname(__DIR__, 2) . '/Frontend/images/uploads';
    if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        $error = 'Could not create the uploads folder (Frontend/images/uploads).';
        return null;
    }

    $base = 'cms_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));

    // SVG is served as-is (it is already tiny and vector).
    if ($ext === 'svg') {
        $name = $base . '.svg';
        if (!@move_uploaded_file((string)$file['tmp_name'], $uploadDir . '/' . $name)) {
            $error = 'Could not save the uploaded image. Check folder permissions.';
            return null;
        }
        return '/Frontend/images/uploads/' . $name;
    }

    // Everything else is re-encoded as WebP so a heavy phone photo does not
    // slow the public site down. If the server cannot do it, keep the original.
    $webpName = $base . '.webp';
    if (cms_convert_to_webp((string)$file['tmp_name'], $uploadDir . '/' . $webpName)) {
        return '/Frontend/images/uploads/' . $webpName;
    }

    $name = $base . '.' . $allowed[$ext];
    if (!@move_uploaded_file((string)$file['tmp_name'], $uploadDir . '/' . $name)) {
        $error = 'Could not save the uploaded image. Check folder permissions.';
        return null;
    }
    return '/Frontend/images/uploads/' . $name;
}

/**
 * Resize (max side 1600px), strip metadata and write a WebP copy.
 *
 * Returns true when a WebP file was written. Falls back silently — the
 * caller keeps the original upload in that case.
 */
function cms_convert_to_webp(string $sourcePath, string $destinationPath, int $maxSide = 1600, int $quality = 82): bool
{
    if (!function_exists('imagewebp') || !function_exists('imagecreatefromstring')) {
        return false; // GD without WebP support
    }
    $raw = @file_get_contents($sourcePath);
    if ($raw === false || $raw === '') {
        return false;
    }

    $image = @imagecreatefromstring($raw);
    if (!$image) {
        return false;
    }

    // Keep transparency for PNG/WebP sources.
    imagealphablending($image, false);
    imagesavealpha($image, true);

    $width = imagesx($image);
    $height = imagesy($image);
    if ($width <= 0 || $height <= 0) {
        imagedestroy($image);
        return false;
    }

    $scale = min(1, $maxSide / max($width, $height));
    if ($scale < 1) {
        $newW = max(1, (int)round($width * $scale));
        $newH = max(1, (int)round($height * $scale));
        $resized = imagecreatetruecolor($newW, $newH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    $ok = @imagewebp($image, $destinationPath, $quality);
    imagedestroy($image);

    return (bool)$ok && is_file($destinationPath) && filesize($destinationPath) > 0;
}
