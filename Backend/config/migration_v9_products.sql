-- ══════════════════════════════════════════════════════════════
-- Migration V9: the real product catalogue (eggs, layer feeds,
-- organic manure & folia) and the end of the demo catalogue.
--
-- The website used to fall back to hardcoded sample products when the
-- table was empty, and the table itself was full of demo rows
-- (broilers, day-old chicks, sample feed). The farm sells eggs, layer
-- feeds and organic fertiliser, so this replaces the catalogue with
-- exactly those products. Order history is preserved: products that
-- appear on past orders are archived (is_active = 0) instead of being
-- deleted, and everything unreferenced is removed.
-- ══════════════════════════════════════════════════════════════

-- Organic fertiliser gets its own product type so it can have its own
-- default image / filtering instead of masquerading as animal feed.
ALTER TABLE products
    MODIFY COLUMN product_type ENUM('live_chicken','meat','eggs','chicks','feed','organic') NOT NULL;

-- Catalogue categories used by the shop filters.
INSERT INTO categories (name, slug, description, category_type)
SELECT 'Feeds', 'feeds', 'Layer, grower and chick mash feeds', 'feed'
WHERE NOT EXISTS (SELECT 1 FROM categories c WHERE c.slug = 'feeds');

INSERT INTO categories (name, slug, description, category_type)
SELECT 'Eggs', 'eggs', 'Farm-fresh table eggs in 30-egg trays', 'feed'
WHERE NOT EXISTS (SELECT 1 FROM categories c WHERE c.slug = 'eggs');

INSERT INTO categories (name, slug, description, category_type)
SELECT 'Organic Fertilizers', 'organic-fertilizers', 'Organic manure and organic folia plant food', 'feed'
WHERE NOT EXISTS (SELECT 1 FROM categories c WHERE c.slug = 'organic-fertilizers');

-- Archive the old catalogue so no demo product shows on the website.
UPDATE products
SET is_active = 0
WHERE slug NOT IN (
    'chick-mash-0-8-weeks','growers-mash-9-12-weeks','developers-mash-13-16-weeks',
    'pre-lay-mash-17-22-weeks','layer-mash-1-23-32-weeks','layer-mash-2-33-100-weeks',
    'tray-of-eggs','organic-manure-50kg','organic-folia-20l',
    'organic-folia-10l','organic-folia-5l','organic-manure-20kg'
);

-- Remove demo products that no order, recipe or schedule points at.
DELETE FROM products
WHERE slug NOT IN (
    'chick-mash-0-8-weeks','growers-mash-9-12-weeks','developers-mash-13-16-weeks',
    'pre-lay-mash-17-22-weeks','layer-mash-1-23-32-weeks','layer-mash-2-33-100-weeks',
    'tray-of-eggs','organic-manure-50kg','organic-folia-20l',
    'organic-folia-10l','organic-folia-5l','organic-manure-20kg'
)
AND id NOT IN (SELECT product_id FROM order_items WHERE product_id IS NOT NULL)
AND id NOT IN (SELECT product_id FROM product_variants WHERE product_id IS NOT NULL)
AND id NOT IN (SELECT product_id FROM feed_recipes WHERE product_id IS NOT NULL)
AND id NOT IN (SELECT product_id FROM recurring_feed_schedules WHERE product_id IS NOT NULL);

-- Drop categories that no longer hold anything (broilers / chicks / layers).
DELETE FROM categories
WHERE slug IN ('broilers', 'layers', 'day-old-chicks')
  AND id NOT IN (SELECT DISTINCT category_id FROM products WHERE category_id IS NOT NULL);

-- ── The catalogue ────────────────────────────────────────────────
INSERT INTO products
    (category_id, name, slug, description, product_type, price, stock_quantity, weight_kg, is_active, is_featured)
SELECT c.id, v.name, v.slug, v.description, v.ptype, v.price, v.stock, v.weight, 1, v.feat
FROM (
    SELECT 'feeds' AS cat_slug, 'Chick Mash (0–8 Weeks)' AS name, 'chick-mash-0-8-weeks' AS slug,
           'Finely ground starter mash for chicks from day one to week 8. Balanced protein and energy for strong early growth. 50kg bag.' AS description,
           'feed' AS ptype, 3200.00 AS price, 150 AS stock, 50.00 AS weight, 1 AS feat
    UNION ALL SELECT 'feeds', 'Growers Mash (9–12 Weeks)', 'growers-mash-9-12-weeks',
           'Grower ration for pullets from week 9 to week 12. Supports steady frame development before the developer phase. 50kg bag.',
           'feed', 3050.00, 150, 50.00, 0
    UNION ALL SELECT 'feeds', 'Developers Mash (13–16 Weeks)', 'developers-mash-13-16-weeks',
           'Developer mash for weeks 13 to 16. Builds body weight and prepares the bird for the pre-lay transition. 50kg bag.',
           'feed', 2950.00, 150, 50.00, 0
    UNION ALL SELECT 'feeds', 'Pre-Lay Mash (17–22 Weeks)', 'pre-lay-mash-17-22-weeks',
           'Pre-lay mash with extra calcium for weeks 17 to 22, so hens come into lay with strong shells from the very first egg. 50kg bag.',
           'feed', 2950.00, 150, 50.00, 0
    UNION ALL SELECT 'feeds', 'Layer Mash 1 (23–32 Weeks)', 'layer-mash-1-23-32-weeks',
           'Peak-production layer mash for weeks 23 to 32. High calcium and protein for large, well-shelled eggs. 50kg bag.',
           'feed', 2900.00, 150, 50.00, 1
    UNION ALL SELECT 'feeds', 'Layer Mash 2 (33–100 Weeks)', 'layer-mash-2-33-100-weeks',
           'Sustain layer mash for weeks 33 to 100. Keeps shell quality and persistence through the rest of the laying cycle. 50kg bag.',
           'feed', 2850.00, 150, 50.00, 0
    UNION ALL SELECT 'eggs', 'Tray of Eggs', 'tray-of-eggs',
           'Farm-fresh grade-A eggs collected daily and packed in a 30-egg tray. Clean, graded and ready for homes, shops and hotels.',
           'eggs', 420.00, 300, 1.80, 1
    UNION ALL SELECT 'organic-fertilizers', '50Kgs Bag Organic Manure', 'organic-manure-50kg',
           'Fully decomposed poultry organic manure in a 50kg bag. Feeds soil life and improves structure for vegetables, maize and fodder.',
           'organic', 500.00, 200, 50.00, 1
    UNION ALL SELECT 'organic-fertilizers', '20Kgs Bag Organic Manure', 'organic-manure-20kg',
           'The same fully decomposed poultry organic manure in a lighter 20kg bag — handy for kitchen gardens and small plots.',
           'organic', 220.00, 200, 20.00, 1
    UNION ALL SELECT 'organic-fertilizers', '20L Organic Folia', 'organic-folia-20l',
           'Organic foliar feed in a 20 litre container for larger farms. Foliar application boosts vigour, flowering and yield.',
           'organic', 3800.00, 120, 20.00, 1
    UNION ALL SELECT 'organic-fertilizers', '10L Organic Folia', 'organic-folia-10l',
           'Organic foliar feed in a 10 litre container. A mid-size option for regular spraying through the season.',
           'organic', 2100.00, 150, 10.00, 0
    UNION ALL SELECT 'organic-fertilizers', '5L Organic Folia', 'organic-folia-5l',
           'Organic foliar feed in a 5 litre container. Ideal for trying it on a garden, greenhouse or a small block.',
           'organic', 1200.00, 180, 5.00, 1
) v
JOIN categories c ON c.slug = v.cat_slug
WHERE NOT EXISTS (SELECT 1 FROM products p WHERE p.slug = v.slug);
