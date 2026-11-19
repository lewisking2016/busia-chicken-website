-- ══════════════════════════════════════════════════════════════
-- Migration V5: Website CMS / Public Site Content
--
-- These tables were previously created only by one-off tools
-- (tools/init_cms_tables.php, settings.sql, schema.sql) which the
-- auto-migration never ran — so a fresh or older database was
-- missing `site_settings` and every CMS save failed with:
--   SQLSTATE[42S02]: Base table or view not found: 1146
--
-- This file is idempotent and safe to run on every request.
-- ══════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────
-- 1. SITE SETTINGS — key/value store read by getSetting() on
--    every public page (brand name, slogan, banner, locations).
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NULL,
    setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
    description VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 2. WEBSITE SLIDERS — homepage hero carousel managed in the CMS
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS website_sliders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slide_order INT NOT NULL DEFAULT 1,
    badge_text VARCHAR(255) DEFAULT 'Welcome to Busia Chicken Farm',
    title_main VARCHAR(255) DEFAULT 'Quality Poultry for',
    title_em VARCHAR(255) DEFAULT 'East Africa',
    subtitle TEXT,
    cta_primary_text VARCHAR(100) DEFAULT 'Explore Products',
    cta_primary_link VARCHAR(255) DEFAULT '/Frontend/pages/shop.php',
    cta_secondary_text VARCHAR(100) DEFAULT 'Our Story',
    cta_secondary_link VARCHAR(255) DEFAULT '/Frontend/pages/about.php',
    stat1_val VARCHAR(50) DEFAULT '100',
    stat1_suffix VARCHAR(20) DEFAULT 'k+',
    stat1_label VARCHAR(100) DEFAULT 'Chickens raised annually',
    stat2_val VARCHAR(50) DEFAULT '5000',
    stat2_suffix VARCHAR(20) DEFAULT '+',
    stat2_label VARCHAR(100) DEFAULT 'Happy customers',
    stat3_val VARCHAR(50) DEFAULT '13',
    stat3_suffix VARCHAR(20) DEFAULT '+',
    stat3_label VARCHAR(100) DEFAULT 'Years in business',
    image_url VARCHAR(255) DEFAULT '/Frontend/images/adbg.webp',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slider_order (slide_order, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 3. TESTIMONIALS — Facebook / customer reviews shown on the site
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_role VARCHAR(100) NULL,
    rating INT DEFAULT 5,
    content TEXT NOT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_testimonial_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 4. Seed brand / contact / location settings (never overwrites
--    values an administrator has already saved).
-- ─────────────────────────────────────────────────────────────
INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_group, description) VALUES
('farm_name',           'Busia Chicken Limited', 'brand',   'Public company name'),
('farm_slogan',         'The Chicken People',    'brand',   'Official slogan'),
('farm_email',          'info@busiachicken.com', 'contact', 'Public contact email'),
('farm_phone',          '+254 727 585 599',      'contact', 'Phone hotline'),
('farm_whatsapp',       '+254727585599',         'contact', 'WhatsApp number'),
('currency',            'KES',                   'general', 'Display currency'),
('announcement_bar',    'Farm-Fresh Grade-A Eggs, Layer Feeds & Organic Manure Available Daily in Nasira & Tangakona! Call +254 727 585 599', 'banner', 'Top announcement bar text'),
('show_announcement',   '1',                     'banner', 'Show the top announcement bar'),
('location_nasira',     'Nasira AC Sub-location, Busibwabo, Busia County, Kenya (Nasira Farm)', 'locations', 'Nasira facility address'),
('hours_nasira',        'Mon - Sat: 7:00 AM - 6:00 PM | Sun: 8:00 AM - 1:00 PM', 'locations', 'Nasira opening hours'),
('google_maps_nasira',  'https://www.google.com/maps/place/0%C2%B026''06.0%22N+34%C2%B012''07.6%22E/@0.4349969,34.1995236,17z/data=!3m1!4b1!4m4!3m3!8m2!3d0.4349969!4d34.2020985?hl=en&entry=ttu&g_ep=EgoyMDI2MTExNi4wIKXMDSoASAFQAw%3D%3D', 'locations', 'Nasira Google Maps link'),
('location_tangakona',  'Tangakona Junction / Market, Busia-Kisumu Highway, Busia County, Kenya (Farm, Feeds & Sales Point)', 'locations', 'Tangakona facility address'),
('hours_tangakona',     'Mon - Sat: 8:00 AM - 6:30 PM | Sun: Closed', 'locations', 'Tangakona opening hours'),
('google_maps_tangakona', 'https://www.google.com/maps?q=0.468734,34.2188207&z=17&hl=en', 'locations', 'Tangakona Google Maps link'),
('mpesa_shortcode',     '',                      'payment', 'M-Pesa paybill / till number'),
('enable_registration', '1',                     'general', 'Allow customer self-registration'),
('maintenance_mode',    '0',                     'system',  'Public site maintenance flag');
