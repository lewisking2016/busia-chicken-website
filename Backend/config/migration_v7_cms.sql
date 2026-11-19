-- ══════════════════════════════════════════════════════════════
-- Migration V7: Editable website content
--
-- Every string and image on the public site becomes a database row,
-- managed from Admin → Website CMS. Nothing is hard-coded in the
-- templates any more: templates call cms('page','key') and fall back
-- to the manifest in Backend/config/cms_defaults.php only when a row
-- is missing.
-- ══════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────
-- 1. CMS CONTENT — one row per editable field, grouped by page
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cms_content (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 2. CMS FAQ ITEMS — questions grouped by category
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cms_faq_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(80) NOT NULL DEFAULT 'General',
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_faq_category (category, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 3. Seed the current FAQ content (only when the table is empty,
--    so administrator edits are never overwritten).
-- ─────────────────────────────────────────────────────────────
INSERT INTO cms_faq_items (category, question, answer, sort_order)
SELECT * FROM (
    SELECT 'Products & Chickens' AS c, 'What breeds of chickens do you offer?' AS q,
           'We offer three main breeds: Ross 308 and Cobb 500 for broilers, ISA Brown for layers, and Lohmann for white egg production. All birds are sourced from certified hatcheries and meet international standards.' AS a, 1 AS o
    UNION ALL SELECT 'Products & Chickens', 'Are your chicks vaccinated?', 'Yes, all day-old chicks are vaccinated against common poultry diseases including Newcastle, Gumboro and Marek''s disease before delivery. We provide vaccination records with each order.', 2
    UNION ALL SELECT 'Products & Chickens', 'What is the difference between broilers and layers?', 'Broilers are meat chickens bred for rapid growth and reach market weight (2-2.5kg) in 6-7 weeks. Layers are egg-laying chickens that produce 300+ eggs per year over 18+ months of production.', 3
    UNION ALL SELECT 'Products & Chickens', 'How do I care for day-old chicks?', 'New chicks need a warm brooder (35C for the first week), clean bedding, starter feed, fresh water and protection from predators. We provide detailed care guides with every order and offer phone support.', 4
    UNION ALL SELECT 'Products & Chickens', 'What is the hatch rate guarantee?', 'We guarantee a 95% hatch rate on all day-old chicks. If it falls below 95%, we provide replacement chicks at no charge. Proper care is essential to achieve this rate.', 5
    UNION ALL SELECT 'Animal Feeds', 'What ingredients are in your feeds?', 'Our feeds contain quality grains (maize), protein sources (soybean meal), minerals, vitamins and probiotics. All ingredients meet food safety standards and are sourced from certified suppliers.', 6
    UNION ALL SELECT 'Animal Feeds', 'When should I switch from starter to grower feed?', 'Switch from starter (0-4 weeks) to grower (4-8 weeks) at 4 weeks of age. For broilers, use finisher feed until market. For layers, switch to layer mash at 16 weeks when they start laying.', 7
    UNION ALL SELECT 'Animal Feeds', 'How much feed does a chicken need daily?', 'Day-old chicks: 5-10g/day. Growing chicks: 20-40g/day. Adult layers: 100-120g/day. Adult broilers: 100-150g/day. Consumption varies by breed, age and environment.', 8
    UNION ALL SELECT 'Animal Feeds', 'How long can I store feed?', 'Store feed in cool, dry conditions away from moisture and pests. Properly stored feed lasts 2-3 months. Once opened, use within 2-4 weeks for optimal nutrition.', 9
    UNION ALL SELECT 'Animal Feeds', 'Do you offer bulk discounts on feed?', 'Orders of 6-20 bags: 5% discount. Orders of 21-50 bags: 10% discount. Orders of 51+ bags: 15% discount. Contact our sales team for custom pricing on very large orders.', 10
    UNION ALL SELECT 'Ordering & Delivery', 'How do I place an order?', 'Order through our website shop, call +254 727 585 599, or visit the farm. Orders are processed immediately and we confirm delivery within 24 hours. Bulk orders should be placed at least 3 days in advance.', 11
    UNION ALL SELECT 'Ordering & Delivery', 'What is the minimum order value?', 'Minimum order is KES 2,000. Free delivery is offered on orders above KES 5,000 within Busia County. Smaller orders within Busia incur a KES 500 delivery charge.', 12
    UNION ALL SELECT 'Ordering & Delivery', 'How quickly can you deliver?', 'Orders within Busia: 1-2 days. Kakamega/Kisumu: 2-3 days. Kisii: 3-4 days. Emergency orders can be arranged for same-day delivery in Busia.', 13
    UNION ALL SELECT 'Ordering & Delivery', 'What payment methods do you accept?', 'We accept M-Pesa, bank transfers and cash on delivery (COD). COD is available for orders within Busia County.', 14
    UNION ALL SELECT 'Ordering & Delivery', 'Can you deliver outside Busia County?', 'Yes. We deliver to Kakamega, Kisumu and Kisii counties. Delivery charges vary by location (KES 500-1,200).', 15
    UNION ALL SELECT 'Payment & Returns', 'Is M-Pesa payment secure?', 'M-Pesa is managed by Safaricom and uses encrypted transactions. We use the official M-Pesa API for all transactions with no hidden charges.', 16
    UNION ALL SELECT 'Payment & Returns', 'What is your return and refund policy?', 'For defective products: full refund within 24 hours of delivery with proof. For chicks: replacement if mortality exceeds 5% in the first 48 hours. For feeds: exchange if damaged on delivery.', 17
    UNION ALL SELECT 'Payment & Returns', 'Do you offer payment plans?', 'For bulk orders (51+ bags or 500+ chicks) we offer 2-week payment terms for established customers.', 18
    UNION ALL SELECT 'Payment & Returns', 'Is there a warranty on the chicks?', 'All chicks come with a 48-hour survival warranty. If mortality exceeds 5%, we provide replacement chicks at no cost, provided proper brooding conditions are maintained.', 19
    UNION ALL SELECT 'Farm Management & Support', 'Do you offer farm consulting services?', 'Yes. We provide free phone support and paid farm visits covering facility design, biosafety protocols, feed optimisation and productivity improvements.', 20
    UNION ALL SELECT 'Farm Management & Support', 'What is the best season to start farming?', 'Poultry farming works year-round, but best results come in dry seasons (Jan-Feb, July-Sept). Rainy seasons require extra biosafety precautions.', 21
    UNION ALL SELECT 'Farm Management & Support', 'How do I join the digital farm management system?', 'Customers can log into the dashboard to track flock records, production data, expenses and recommendations. It is free for all customers.', 22
    UNION ALL SELECT 'Farm Management & Support', 'What should I do if birds get sick?', 'Call us immediately on +254 727 585 599. Isolate sick birds, improve ventilation and ensure clean water. Early treatment saves lives and prevents spread.', 23
    UNION ALL SELECT 'Farm Management & Support', 'Can I visit the farm?', 'Yes. Farm visits are welcome by appointment for biosafety reasons. Call +254 727 585 599 to schedule.', 24
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM cms_faq_items);
