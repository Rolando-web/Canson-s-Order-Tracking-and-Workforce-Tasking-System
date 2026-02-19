-- ============================================
-- Canson School & Office Supplies
-- Database Schema
-- Generated: February 20, 2026
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------
-- 1. Users Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL COMMENT 'Username (unique)',
    `role` ENUM('employee', 'admin', 'super_admin') NOT NULL DEFAULT 'employee' COMMENT 'Role type',
    `password` VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    `remember_token` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Session persistence token',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 2. Sessions Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`),
    CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 3. Inventory Items Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `inventory_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL COMMENT 'Item name',
    `item_id` VARCHAR(255) NOT NULL COMMENT 'Unique code e.g. INV-001',
    `category` VARCHAR(255) NOT NULL COMMENT 'Finished Goods, Raw Materials, Packaging Materials',
    `stock` INT NOT NULL DEFAULT 0 COMMENT 'Current stock quantity',
    `unit` VARCHAR(255) NOT NULL COMMENT 'pcs, reams, sheets, liters, kg, rolls, boxes',
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price per unit',
    `status` VARCHAR(255) NOT NULL DEFAULT 'In Stock' COMMENT 'Stock status',
    `is_best_seller` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether item is a best seller',
    `image_path` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Product image path',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `inventory_items_item_id_unique` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 4. Stock Transactions Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `stock_transactions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `item_id` BIGINT UNSIGNED NOT NULL COMMENT 'Reference to inventory_items.id',
    `transaction_type` ENUM('stock_in', 'stock_out') NOT NULL COMMENT 'Type of transaction',
    `quantity` INT NOT NULL COMMENT 'Quantity of stock moved',
    `previous_stock` INT NOT NULL COMMENT 'Stock level before transaction',
    `new_stock` INT NOT NULL COMMENT 'Stock level after transaction',
    `reference_number` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Auto-generated SI-YYYY-NNNN / SO-YYYY-NNNN',
    `supplier` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Supplier name (stock in only)',
    `reason` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Reason for stock out',
    `notes` TEXT NULL DEFAULT NULL COMMENT 'Additional transaction notes',
    `transaction_date` DATE NOT NULL COMMENT 'Date of the transaction',
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Who performed the transaction',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `stock_transactions_item_id_index` (`item_id`),
    KEY `stock_transactions_created_by_index` (`created_by`),
    CONSTRAINT `stock_transactions_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `stock_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 5. Orders Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` VARCHAR(20) NOT NULL COMMENT 'Human-readable order ID e.g. ORD-001',
    `customer_name` VARCHAR(100) NOT NULL COMMENT 'Name of the customer',
    `contact_number` VARCHAR(11) NOT NULL COMMENT 'Customer contact number',
    `delivery_address` TEXT NOT NULL COMMENT 'Full delivery address',
    `delivery_date` DATE NOT NULL COMMENT 'Expected delivery date',
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total order amount',
    `status` ENUM('Pending', 'In-Progress', 'Completed') NOT NULL DEFAULT 'Pending' COMMENT 'Order status',
    `priority` ENUM('Normal', 'High', 'Urgent') NOT NULL DEFAULT 'Normal' COMMENT 'Priority level',
    `assigned` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Assigned person name',
    `notes` TEXT NULL DEFAULT NULL COMMENT 'Special instructions or notes',
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Who created the order',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `orders_order_id_unique` (`order_id`),
    KEY `orders_created_by_index` (`created_by`),
    CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 6. Order Items Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` BIGINT UNSIGNED NOT NULL COMMENT 'Reference to orders.id',
    `inventory_item_id` BIGINT UNSIGNED NOT NULL COMMENT 'Reference to inventory_items.id',
    `name` VARCHAR(255) NOT NULL COMMENT 'Item name',
    `quantity` INT NOT NULL COMMENT 'Quantity ordered',
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price per unit',
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Line subtotal',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `order_items_order_id_index` (`order_id`),
    KEY `order_items_inventory_item_id_index` (`inventory_item_id`),
    CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `order_items_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 7. Dispatches Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `dispatches` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Reference to orders.id',
    `customer` VARCHAR(100) NOT NULL COMMENT 'Customer name',
    `items` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Items summary',
    `address` TEXT NOT NULL COMMENT 'Delivery address',
    `driver` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Assigned driver name',
    `vehicle` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Vehicle details',
    `dispatch_time` DATETIME NULL DEFAULT NULL COMMENT 'When dispatch started',
    `delivery_time` DATETIME NULL DEFAULT NULL COMMENT 'When delivery was completed',
    `status` ENUM('pending', 'in_transit', 'delivered', 'failed') NOT NULL DEFAULT 'pending' COMMENT 'Dispatch status',
    `date` DATE NOT NULL COMMENT 'Scheduled delivery date',
    `assigned_by` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Who assigned the dispatch',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `dispatches_order_id_index` (`order_id`),
    KEY `dispatches_assigned_by_index` (`assigned_by`),
    CONSTRAINT `dispatches_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
    CONSTRAINT `dispatches_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 8. Assignments Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `assignments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` VARCHAR(20) NOT NULL COMMENT 'Reference to orders.order_id',
    `employee_id` BIGINT UNSIGNED NOT NULL COMMENT 'Assigned worker',
    `priority` ENUM('normal', 'high', 'urgent') NOT NULL DEFAULT 'normal' COMMENT 'Priority level',
    `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Assignment status',
    `notes` TEXT NULL DEFAULT NULL COMMENT 'Assignment notes or instructions',
    `assigned_by` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Who assigned',
    `assigned_date` DATE NOT NULL COMMENT 'When assignment was made',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `assignments_employee_id_index` (`employee_id`),
    KEY `assignments_assigned_by_index` (`assigned_by`),
    CONSTRAINT `assignments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 9. Schedule Notes Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `schedule_notes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(100) NOT NULL COMMENT 'Title of the schedule event',
    `description` TEXT NULL DEFAULT NULL COMMENT 'Detailed description',
    `schedule_date` DATE NOT NULL COMMENT 'Date of the scheduled event',
    `start_time` TIME NULL DEFAULT NULL COMMENT 'Start time (if not all-day)',
    `end_time` TIME NULL DEFAULT NULL COMMENT 'End time (if not all-day)',
    `is_all_day` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether event is all day',
    `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium' COMMENT 'Priority level',
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Who created',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `schedule_notes_created_by_index` (`created_by`),
    CONSTRAINT `schedule_notes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 10. Activity Logs Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Who performed the action',
    `action` VARCHAR(50) NOT NULL COMMENT 'Action type',
    `description` TEXT NOT NULL COMMENT 'Detailed description of the action',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `activity_logs_user_id_index` (`user_id`),
    CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
