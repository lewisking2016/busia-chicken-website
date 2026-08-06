-- Migration V5: Admin Modules and Activity Tables

CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `assigned_to` INT NULL,
    `due_date` DATE NULL,
    `status` ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `created_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sender_id` INT NULL,
    `recipient_id` INT NULL,
    `subject` VARCHAR(150) NOT NULL,
    `body` TEXT NOT NULL,
    `status` ENUM('pending', 'sent', 'read') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`recipient_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `log_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `level` ENUM('INFO', 'WARNING', 'ERROR', 'DEBUG') NOT NULL DEFAULT 'INFO',
    `message` TEXT NOT NULL,
    `context` VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `herds` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `species` VARCHAR(100) DEFAULT NULL,
    `size` INT DEFAULT 0,
    `location` VARCHAR(200) DEFAULT NULL,
    `status` ENUM('Active', 'Sold', 'Archived') DEFAULT 'Active',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `animals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tag` VARCHAR(100) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `type` VARCHAR(100) DEFAULT NULL,
    `breed` VARCHAR(100) DEFAULT NULL,
    `gender` VARCHAR(50) DEFAULT NULL,
    `birth_date` DATE DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Active',
    `herd_id` INT DEFAULT NULL,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`herd_id`) REFERENCES `herds`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `breeding_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100) DEFAULT NULL,
    `male_parent` VARCHAR(150) DEFAULT NULL,
    `date` DATE DEFAULT NULL,
    `due_date` DATE DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Pending',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `health_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100) DEFAULT NULL,
    `product` VARCHAR(150) DEFAULT NULL,
    `date` DATE DEFAULT NULL,
    `next_date` DATE DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Scheduled',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `farm_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `item_type` VARCHAR(100) NOT NULL,
    `species` VARCHAR(100) DEFAULT NULL,
    `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `stock_quantity` INT DEFAULT 0,
    `status` ENUM('active', 'out_of_stock', 'inactive') DEFAULT 'active',
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE `financial_records`
    ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(50) DEFAULT 'Cash',
    ADD COLUMN IF NOT EXISTS `payment_status` ENUM('Pending', 'Approved', 'Failed', 'Completed') DEFAULT 'Pending';

ALTER TABLE `raw_materials`
    ADD COLUMN IF NOT EXISTS `feed_type` VARCHAR(100) DEFAULT 'Feed';

ALTER TABLE `users`
    MODIFY COLUMN `role` ENUM('super_admin', 'farm_manager', 'stock_manager', 'customer') DEFAULT 'customer';
