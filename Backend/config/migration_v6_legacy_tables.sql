-- ══════════════════════════════════════════════════════════════
-- Migration V6: tables that live code uses but nothing created
--
-- Backend/api/admin_stock.php reads and writes `production_history`
-- and `stock_alerts`, and migration_v2.sql/v3.sql ALTER both — but no
-- migration file ever CREATEs them. On any database built from the
-- migration sources those modules failed with
--   SQLSTATE[42S02]: Base table or view not found: 1146
--
-- `recipe_ingredients` is the name admin_stock.php queries, while the
-- schema calls the same table `feed_recipe_ingredients`. The view
-- below keeps the legacy query working without duplicating data.
-- ══════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────
-- 1. PRODUCTION HISTORY — one row per finished-feed batch produced
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS production_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_number VARCHAR(30) DEFAULT NULL,
    recipe_id INT DEFAULT NULL,
    bag_size_kg DECIMAL(6,2) NOT NULL DEFAULT 0,
    quantity_bags INT NOT NULL DEFAULT 0,
    total_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    produced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_history_recipe (recipe_id),
    INDEX idx_history_date (produced_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 2. STOCK ALERTS — low stock / price / margin warnings
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS stock_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM(
        'low_stock', 'price_fluctuation', 'bottleneck',
        'lead_time_exhaustion', 'margin_protection'
    ) NOT NULL DEFAULT 'low_stock',
    message TEXT NULL,
    related_id INT NULL,
    is_resolved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_alert_resolved (is_resolved),
    INDEX idx_alert_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 3. COMPATIBILITY VIEW — legacy name for feed_recipe_ingredients
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW recipe_ingredients AS
    SELECT
        id,
        recipe_id,
        raw_material_id,
        amount_per_bag_kg AS amount_kg
    FROM feed_recipe_ingredients;
