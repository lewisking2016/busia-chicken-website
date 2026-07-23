-- Stock Management Module Tables

USE busia_chicken_db;

-- 1. Raw Materials Table
CREATE TABLE IF NOT EXISTS raw_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    stock_tons DECIMAL(10, 3) DEFAULT 0.000,
    current_price_per_ton DECIMAL(10, 2) DEFAULT 0.00,
    min_stock_level DECIMAL(10, 3) DEFAULT 1.000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Feed Recipes Table
CREATE TABLE IF NOT EXISTS feed_recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    base_bag_size_kg DECIMAL(5, 2) DEFAULT 70.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Recipe Ingredients Table
CREATE TABLE IF NOT EXISTS recipe_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    raw_material_id INT NOT NULL,
    amount_kg DECIMAL(8, 3) NOT NULL, -- Amount needed for 1 bag of base_bag_size_kg
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES feed_recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (raw_material_id) REFERENCES raw_materials(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Production History Table
CREATE TABLE IF NOT EXISTS production_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    bag_size_kg DECIMAL(5, 2) NOT NULL,
    quantity_bags INT NOT NULL,
    total_cost DECIMAL(10, 2) NOT NULL,
    produced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES feed_recipes(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. Stock Management Alerts Table
CREATE TABLE IF NOT EXISTS stock_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM('low_stock', 'price_fluctuation', 'bottleneck') NOT NULL,
    message TEXT NOT NULL,
    related_id INT, -- ID of raw material or recipe
    is_resolved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert some initial raw materials for testing
INSERT IGNORE INTO raw_materials (id, name, stock_tons, current_price_per_ton) VALUES
(1, 'Maize', 10.000, 35000.00),
(2, 'Soya Meal', 5.000, 85000.00),
(3, 'Fish Meal', 2.000, 120000.00),
(4, 'Limestone', 1.500, 15000.00),
(5, 'Premix', 0.500, 250000.00);

-- Insert sample recipes for existing feed products
INSERT IGNORE INTO feed_recipes (id, product_id, name, base_bag_size_kg) VALUES
(1, 11, 'Grower Feed Standard', 70.00),
(2, 13, 'Broiler Finisher Standard', 70.00),
(3, 14, 'Premium Mix Signature', 70.00);

-- Insert recipe ingredients (kg per 70kg bag)
INSERT IGNORE INTO recipe_ingredients (recipe_id, raw_material_id, amount_kg) VALUES
-- Grower Feed: 40kg Maize, 20kg Soya, 5kg Fish, 4kg Lime, 1kg Premix
(1, 1, 40.00), (1, 2, 20.00), (1, 3, 5.00), (1, 4, 4.00), (1, 5, 1.00),
-- Broiler Finisher: 45kg Maize, 15kg Soya, 6kg Fish, 3kg Lime, 1kg Premix
(2, 1, 45.00), (2, 2, 15.00), (2, 3, 6.00), (2, 4, 3.00), (2, 5, 1.00),
-- Premium Mix: 35kg Maize, 25kg Soya, 6kg Fish, 3kg Lime, 1kg Premix
(3, 1, 35.00), (3, 2, 25.00), (3, 3, 6.00), (3, 4, 3.00), (3, 5, 1.00);

