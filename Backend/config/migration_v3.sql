-- Migration V3: Incoming Stock (Procurement) & Raw Material Sales Integration

-- 1. Add Safety Reserve column to raw_materials table (in kgs)
ALTER TABLE raw_materials 
    ADD COLUMN reserved_production_kg DECIMAL(12, 2) DEFAULT 0.00 AFTER min_stock_level;

-- 2. Add raw_material_id to products table to associate raw materials directly for retail sale
ALTER TABLE products 
    ADD COLUMN raw_material_id INT DEFAULT NULL AFTER category_id,
    ADD CONSTRAINT fk_products_raw_material FOREIGN KEY (raw_material_id) REFERENCES raw_materials(id) ON DELETE SET NULL;

-- 3. Create suppliers table for auto-procurement
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_name VARCHAR(100),
    phone VARCHAR(30),
    email VARCHAR(100),
    address TEXT,
    lead_time_days INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Create incoming_shipments table to track raw material deliveries
CREATE TABLE IF NOT EXISTS incoming_shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    raw_material_id INT NOT NULL,
    quantity_kg DECIMAL(12, 2) NOT NULL,
    cost_per_kg DECIMAL(10, 2) NOT NULL,
    expected_delivery_date DATE,
    status ENUM('ordered', 'in_transit', 'delivered', 'cancelled') DEFAULT 'ordered',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (raw_material_id) REFERENCES raw_materials(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Expand stock_alerts type list
ALTER TABLE stock_alerts MODIFY COLUMN alert_type ENUM('low_stock', 'price_fluctuation', 'bottleneck', 'lead_time_exhaustion', 'margin_protection') NOT NULL;
