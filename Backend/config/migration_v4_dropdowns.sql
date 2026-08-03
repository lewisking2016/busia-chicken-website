-- Migration v4: System Dropdowns & Master Data Management
CREATE TABLE IF NOT EXISTS `system_dropdowns` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_key` VARCHAR(50) NOT NULL,
  `group_label` VARCHAR(100) NOT NULL,
  `option_value` VARCHAR(100) NOT NULL,
  `option_label` VARCHAR(100) NOT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_system` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_group_key` (`group_key`),
  UNIQUE KEY `unique_group_option` (`group_key`, `option_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Default Dropdown Groups and Options
INSERT IGNORE INTO `system_dropdowns` (`group_key`, `group_label`, `option_value`, `option_label`, `sort_order`, `is_active`, `is_system`) VALUES
-- Product Categories
('product_categories', 'Product Categories', 'broilers', 'Broilers', 1, 1, 1),
('product_categories', 'Product Categories', 'layers', 'Layers', 2, 1, 1),
('product_categories', 'Product Categories', 'day-old-chicks', 'Day-Old Chicks', 3, 1, 1),
('product_categories', 'Product Categories', 'feeds', 'Feeds', 4, 1, 1),

-- Product Types
('product_types', 'Product Types', 'live_chicken', 'Live Chicken', 1, 1, 1),
('product_types', 'Product Types', 'eggs', 'Eggs', 2, 1, 1),
('product_types', 'Product Types', 'chicks', 'Chicks', 3, 1, 1),
('product_types', 'Product Types', 'feed', 'Feeds', 4, 1, 1),

-- Order Statuses
('order_statuses', 'Order Statuses', 'pending', 'Pending', 1, 1, 1),
('order_statuses', 'Order Statuses', 'paid', 'Paid', 2, 1, 1),
('order_statuses', 'Order Statuses', 'picking', 'Picking', 3, 1, 1),
('order_statuses', 'Order Statuses', 'packing', 'Packing', 4, 1, 1),
('order_statuses', 'Order Statuses', 'production', 'In Production', 5, 1, 1),
('order_statuses', 'Order Statuses', 'dispatch', 'Dispatch', 6, 1, 1),
('order_statuses', 'Order Statuses', 'shipped', 'Shipped', 7, 1, 1),
('order_statuses', 'Order Statuses', 'delivered', 'Delivered', 8, 1, 1),
('order_statuses', 'Order Statuses', 'completed', 'Completed', 9, 1, 1),
('order_statuses', 'Order Statuses', 'cancelled', 'Cancelled', 10, 1, 1),

-- Contact Form Subjects
('contact_subjects', 'Contact Form Subjects', 'General Inquiry', 'General Inquiry', 1, 1, 0),
('contact_subjects', 'Contact Form Subjects', 'Bulk Order Inquiry', 'Bulk Order Inquiry', 2, 1, 0),
('contact_subjects', 'Contact Form Subjects', 'Partnership / Wholesale', 'Partnership / Wholesale', 3, 1, 0),
('contact_subjects', 'Contact Form Subjects', 'Feedback & Support', 'Feedback & Support', 4, 1, 0),
('contact_subjects', 'Contact Form Subjects', 'Stock Availability', 'Stock Availability', 5, 1, 0),

-- Measurement Units
('units', 'Units of Measurement', 'kg', 'Kilograms (kg)', 1, 1, 0),
('units', 'Units of Measurement', 'g', 'Grams (g)', 2, 1, 0),
('units', 'Units of Measurement', 'bag', 'Bags (bag)', 3, 1, 0),
('units', 'Units of Measurement', 'pcs', 'Pieces / Birds (pcs)', 4, 1, 0),
('units', 'Units of Measurement', 'liters', 'Liters (L)', 5, 1, 0),
('units', 'Units of Measurement', 'trays', 'Trays (trays)', 6, 1, 0),

-- User Roles
('user_roles', 'User Roles', 'admin', 'Administrator', 1, 1, 1),
('user_roles', 'User Roles', 'stock_manager', 'Stock Manager', 2, 1, 1),
('user_roles', 'User Roles', 'customer', 'Customer', 3, 1, 1),

-- Shipment Statuses
('shipment_statuses', 'Shipment Statuses', 'ordered', 'Ordered / Draft', 1, 1, 1),
('shipment_statuses', 'Shipment Statuses', 'in_transit', 'In Transit', 2, 1, 1),
('shipment_statuses', 'Shipment Statuses', 'delivered', 'Delivered (Auto-adjusts Stock & Cost!)', 3, 1, 1),
('shipment_statuses', 'Shipment Statuses', 'cancelled', 'Cancelled', 4, 1, 1),

-- Flock Statuses
('flock_statuses', 'Flock Statuses', 'active', 'Active', 1, 1, 1),
('flock_statuses', 'Flock Statuses', 'sold', 'Sold', 2, 1, 1),
('flock_statuses', 'Flock Statuses', 'archived', 'Archived', 3, 1, 1),

-- Vaccination Statuses
('vaccination_statuses', 'Vaccination Statuses', 'scheduled', 'Scheduled', 1, 1, 1),
('vaccination_statuses', 'Vaccination Statuses', 'completed', 'Completed', 2, 1, 1),
('vaccination_statuses', 'Vaccination Statuses', 'missed', 'Missed', 3, 1, 1),

-- Expense Categories
('expense_categories', 'Expense Categories', 'feed_purchase', 'Feed Purchase', 1, 1, 0),
('expense_categories', 'Expense Categories', 'chicks_livestock', 'Chicks / Livestock', 2, 1, 0),
('expense_categories', 'Expense Categories', 'vaccines_drugs', 'Vaccines & Drugs', 3, 1, 0),
('expense_categories', 'Expense Categories', 'farm_labor', 'Farm Labor / Wages', 4, 1, 0),
('expense_categories', 'Expense Categories', 'utilities', 'Utilities (Water/Power)', 5, 1, 0),
('expense_categories', 'Expense Categories', 'packaging', 'Packaging & Bags', 6, 1, 0),
('expense_categories', 'Expense Categories', 'transport', 'Transportation / Fuel', 7, 1, 0),
('expense_categories', 'Expense Categories', 'equipment_repairs', 'Equipment Repairs', 8, 1, 0),
('expense_categories', 'Expense Categories', 'other', 'Other Operations', 9, 1, 0);

