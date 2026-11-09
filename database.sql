-- Multi-Vendor E-Commerce Database Schema
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `ecommerce_mv` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce_mv`;

CREATE TABLE `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `is_affiliate` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `sellers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `shop_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `shop_address` TEXT NOT NULL,
  `shop_logo` VARCHAR(255) DEFAULT NULL,
  `valid_id` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  `total_sales` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `available_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `rating` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `total_reviews` INT UNSIGNED NOT NULL DEFAULT 0,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `seller_applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id` INT UNSIGNED NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `reviewed_by` INT UNSIGNED DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`seller_id`) REFERENCES `sellers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `brands` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `logo` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `brand_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(280) NOT NULL,
  `description` TEXT NOT NULL,
  `short_description` VARCHAR(500) DEFAULT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `discount_price` DECIMAL(12,2) DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `sku` VARCHAR(50) DEFAULT NULL,
  `weight` DECIMAL(8,2) DEFAULT NULL,
  `rating` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `total_reviews` INT UNSIGNED NOT NULL DEFAULT 0,
  `sold_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_flash_sale` TINYINT(1) NOT NULL DEFAULT 0,
  `flash_sale_end` DATETIME DEFAULT NULL,
  `status` ENUM('active','inactive','out_of_stock','pending') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_seller` (`seller_id`),
  INDEX `idx_category` (`category_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`seller_id`) REFERENCES `sellers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `product_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `addresses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(50) DEFAULT 'Home',
  `recipient_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address_line` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `province` VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `latitude` DECIMAL(10,8) DEFAULT NULL,
  `longitude` DECIMAL(11,8) DEFAULT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `delivery_locations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `latitude` DECIMAL(10,8) NOT NULL,
  `longitude` DECIMAL(11,8) NOT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `carts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cart_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cart_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `price` DECIMAL(12,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_product` (`cart_id`,`product_id`),
  FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `wishlists` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `seller_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('pending','confirmed','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` ENUM('cod','gcash') NOT NULL,
  `payment_status` ENUM('pending','paid','failed','refunded','awaiting_verification') NOT NULL DEFAULT 'pending',
  `subtotal` DECIMAL(12,2) NOT NULL,
  `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `coupon_code` VARCHAR(50) DEFAULT NULL,
  `affiliate_id` INT UNSIGNED DEFAULT NULL,
  `affiliate_code` VARCHAR(50) DEFAULT NULL,
  `shipping_name` VARCHAR(100) NOT NULL,
  `shipping_phone` VARCHAR(20) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_city` VARCHAR(100) DEFAULT NULL,
  `shipping_province` VARCHAR(100) DEFAULT NULL,
  `shipping_lat` DECIMAL(10,8) DEFAULT NULL,
  `shipping_lng` DECIMAL(11,8) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `delivered_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `seller_id` INT UNSIGNED NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `product_price` DECIMAL(12,2) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `payment_method` ENUM('cod','gcash') NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `reference_number` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending','verified','rejected','refunded') NOT NULL DEFAULT 'pending',
  `verified_by` INT UNSIGNED DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `payment_receipts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` INT UNSIGNED NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED DEFAULT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `title` VARCHAR(150) DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `affiliate_accounts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `affiliate_code` VARCHAR(30) NOT NULL UNIQUE,
  `status` ENUM('pending','active','suspended') NOT NULL DEFAULT 'active',
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 5.00,
  `total_clicks` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_referrals` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_orders` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_earnings` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `pending_earnings` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `available_earnings` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `affiliate_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `unique_code` VARCHAR(50) NOT NULL UNIQUE,
  `clicks` INT UNSIGNED NOT NULL DEFAULT 0,
  `conversions` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `affiliate_clicks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` INT UNSIGNED NOT NULL,
  `link_id` INT UNSIGNED DEFAULT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `referrer` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `affiliate_commissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` INT UNSIGNED NOT NULL,
  `order_id` INT UNSIGNED NOT NULL,
  `order_item_id` INT UNSIGNED DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `rate` DECIMAL(5,2) NOT NULL,
  `status` ENUM('pending','approved','paid','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_accounts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `affiliate_withdrawals` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `affiliate_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `method` VARCHAR(50) NOT NULL DEFAULT 'gcash',
  `account_details` TEXT NOT NULL,
  `status` ENUM('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  `processed_by` INT UNSIGNED DEFAULT NULL,
  `processed_at` DATETIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `coupons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `value` DECIMAL(10,2) NOT NULL,
  `min_order` DECIMAL(10,2) DEFAULT 0.00,
  `max_discount` DECIMAL(10,2) DEFAULT NULL,
  `usage_limit` INT DEFAULT NULL,
  `used_count` INT NOT NULL DEFAULT 0,
  `starts_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `seller_id` INT UNSIGNED DEFAULT NULL,
  `admin_id` INT UNSIGNED DEFAULT NULL,
  `type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `inventory_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `seller_id` INT UNSIGNED NOT NULL,
  `change_qty` INT NOT NULL,
  `reason` VARCHAR(100) DEFAULT NULL,
  `reference` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `website_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `recently_viewed` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@techmoto.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin');

INSERT INTO `categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Computer Parts', 'computer-parts', 'PC components, processors, GPUs, RAM, storage and accessories', 1),
('Motorcycle Parts', 'motorcycle-parts', 'Motorcycle helmets, lights, tires, engine parts and accessories', 2),
('Processors', 'processors', 'CPU processors from AMD and Intel', 3),
('Graphics Cards', 'graphics-cards', 'NVIDIA and AMD graphics cards', 4),
('Memory & Storage', 'memory-storage', 'RAM and SSD storage solutions', 5),
('PC Accessories', 'pc-accessories', 'Keyboards, mice, monitors and cases', 6),
('Helmets & Safety', 'helmets-safety', 'Motorcycle helmets and protective gear', 7),
('Lighting', 'lighting', 'Motorcycle headlights, taillights and LED accessories', 8),
('Engine & Performance', 'engine-performance', 'Engine oil, chains, brake pads and performance parts', 9),
('Motorcycle Accessories', 'motorcycle-accessories', 'Phone holders, top boxes, raincoats and more', 10);

INSERT INTO `brands` (`name`, `slug`) VALUES
('AMD', 'amd'),('Intel', 'intel'),('ASUS', 'asus'),('NVIDIA', 'nvidia'),
('Kingston', 'kingston'),('Samsung', 'samsung'),('MSI', 'msi'),('Corsair', 'corsair'),
('Generic', 'generic'),('Shoei', 'shoei'),('AGV', 'agv');

INSERT INTO `website_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'TechMoto PH'),
('site_tagline', 'Your One-Stop Shop for Computer & Motorcycle Parts'),
('site_email', 'support@techmoto.ph'),
('site_phone', '+63 917 123 4567'),
('site_address', '123 Tech Avenue, Makati City, Metro Manila, Philippines'),
('gcash_number', '09171234567'),
('gcash_name', 'TechMoto PH Official'),
('store_lat', '14.5547'),
('store_lng', '121.0244'),
('affiliate_commission_rate', '5.00'),
('shipping_fee', '50.00'),
('free_shipping_min', '2000.00'),
('currency', 'PHP'),
('currency_symbol', '₱');

INSERT INTO `sellers` (`shop_name`, `email`, `phone`, `password`, `shop_address`, `status`, `commission_rate`, `approved_at`) VALUES
('TechHub Store', 'seller1@techmoto.ph', '09181234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Unit 5, Cyber Mall, Quezon City', 'approved', 10.00, NOW()),
('MotoGear PH', 'seller2@techmoto.ph', '09191234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '45 Highway Ave, Pasig City', 'approved', 10.00, NOW());

INSERT INTO `products` (`seller_id`, `category_id`, `brand_id`, `name`, `slug`, `description`, `short_description`, `price`, `discount_price`, `stock`, `sku`, `rating`, `total_reviews`, `sold_count`, `is_featured`, `status`) VALUES
(1, 3, 1, 'AMD Ryzen 5 5600', 'amd-ryzen-5-5600', 'AMD Ryzen 5 5600 6-core 12-thread desktop processor with Wraith Stealth cooler. Base clock 3.5GHz, boost up to 4.4GHz.', '6-core processor with excellent multi-thread performance', 5499.00, NULL, 45, 'CPU-AMD-5600', 4.7, 128, 342, 1, 'active'),
(1, 3, 2, 'Intel Core i5-12400F', 'intel-core-i5-12400f', 'Intel Core i5-12400F 6-core 12-thread processor. Up to 4.4GHz turbo.', '12th Gen Intel Core i5 processor', 8999.00, 8499.00, 30, 'CPU-INT-12400F', 4.6, 95, 210, 1, 'active'),
(1, 4, 3, 'ASUS Dual GeForce RTX 3060', 'asus-rtx-3060', 'ASUS Dual GeForce RTX 3060 12GB GDDR6. Excellent 1080p and 1440p gaming performance.', '12GB GDDR6 graphics card for modern gaming', 18999.00, 17999.00, 18, 'GPU-ASUS-3060', 4.8, 210, 156, 1, 'active'),
(1, 4, 4, 'NVIDIA GeForce RTX 4060', 'nvidia-rtx-4060', 'NVIDIA GeForce RTX 4060 8GB GDDR6. Next-gen Ada Lovelace architecture with DLSS 3.', 'Latest generation RTX graphics card', 19999.00, NULL, 22, 'GPU-NV-4060', 4.9, 87, 98, 1, 'active'),
(1, 5, 5, 'Kingston Fury Beast 8GB DDR4', 'kingston-8gb-ddr4', 'Kingston Fury Beast 8GB DDR4-3200MHz CL16 desktop memory.', '8GB DDR4-3200 RAM module', 1099.00, 999.00, 120, 'RAM-KST-8GB', 4.5, 340, 890, 0, 'active'),
(1, 5, 5, 'Kingston Fury Beast 16GB DDR4', 'kingston-16gb-ddr4', 'Kingston Fury Beast 16GB (2x8GB) DDR4-3200MHz kit.', '16GB DDR4 dual-channel kit', 1899.00, 1799.00, 85, 'RAM-KST-16GB', 4.7, 256, 670, 1, 'active'),
(1, 5, 6, 'Samsung 970 EVO Plus 500GB NVMe', 'samsung-970-evo-500gb', 'Samsung 970 EVO Plus 500GB M.2 NVMe SSD. Sequential read up to 3,500 MB/s.', '500GB high-speed NVMe SSD', 2999.00, 2799.00, 60, 'SSD-SAM-500', 4.8, 412, 1250, 1, 'active'),
(1, 5, 6, '1TB NVMe SSD', '1tb-nvme-ssd', '1TB PCIe Gen3 NVMe M.2 SSD. High capacity storage with fast sequential speeds.', '1TB capacity NVMe solid state drive', 3499.00, 3299.00, 40, 'SSD-1TB-NVME', 4.6, 178, 420, 0, 'active'),
(1, 6, 7, 'MSI B550M PRO-VDH WIFI', 'msi-b550m-motherboard', 'MSI B550M PRO-VDH WIFI Micro-ATX motherboard. Supports AMD Ryzen 5000 series.', 'B550 chipset motherboard with WiFi', 5299.00, NULL, 25, 'MB-MSI-B550', 4.5, 89, 145, 0, 'active'),
(1, 6, 8, '650W 80+ Bronze PSU', '650w-80bronze-psu', '650W 80 Plus Bronze certified power supply. Semi-modular design.', 'Reliable 650W power supply unit', 2499.00, 2299.00, 55, 'PSU-650W', 4.4, 156, 380, 0, 'active'),
(1, 6, NULL, 'Gaming Mechanical Keyboard', 'gaming-mechanical-keyboard', 'RGB mechanical gaming keyboard with blue switches.', 'RGB mechanical keyboard for gamers', 1299.00, 1099.00, 70, 'KB-MECH-RGB', 4.3, 220, 510, 0, 'active'),
(1, 6, NULL, 'Gaming Mouse', 'gaming-mouse', 'Lightweight gaming mouse with 12,000 DPI optical sensor.', 'High DPI gaming mouse', 599.00, 499.00, 95, 'MS-GAME', 4.4, 310, 780, 0, 'active'),
(1, 6, NULL, '24-inch Gaming Monitor', '24inch-gaming-monitor', '24-inch Full HD 165Hz IPS gaming monitor with 1ms response time.', '165Hz Full HD gaming monitor', 5999.00, 5499.00, 20, 'MON-24-165', 4.7, 145, 230, 1, 'active'),
(1, 6, NULL, 'CPU Air Cooler', 'cpu-air-cooler', 'Tower air cooler with dual heat pipes and quiet 120mm PWM fan.', 'Efficient dual-tower air cooler', 899.00, NULL, 48, 'COOL-AIR', 4.5, 98, 190, 0, 'active'),
(1, 6, NULL, 'Mid-Tower PC Case', 'mid-tower-pc-case', 'ATX mid-tower case with tempered glass side panel.', 'Stylish mid-tower case with glass panel', 1799.00, 1599.00, 35, 'CASE-MID', 4.6, 112, 260, 0, 'active'),
(2, 7, NULL, 'Motorcycle Helmet', 'motorcycle-helmet', 'DOT-approved full-face motorcycle helmet with anti-fog visor.', 'Full-face DOT certified helmet', 1499.00, 1299.00, 50, 'HLM-FULL', 4.6, 280, 620, 1, 'active'),
(2, 8, NULL, 'Motorcycle LED Headlight', 'motorcycle-led-headlight', 'High-brightness LED headlight bulb compatible with most motorcycles.', 'Bright LED headlight upgrade', 599.00, 499.00, 80, 'LED-HL', 4.5, 195, 450, 0, 'active'),
(2, 10, NULL, 'Motorcycle Side Mirror', 'motorcycle-side-mirror', 'Universal motorcycle side mirror pair with adjustable angle.', 'Pair of universal side mirrors', 299.00, NULL, 110, 'MIR-SIDE', 4.3, 140, 380, 0, 'active'),
(2, 9, NULL, 'Motorcycle Brake Pads', 'motorcycle-brake-pads', 'High-quality ceramic brake pads for excellent stopping power.', 'Ceramic compound brake pads', 399.00, 349.00, 90, 'BRK-PAD', 4.4, 167, 410, 0, 'active'),
(2, 9, NULL, 'Motorcycle Chain Set', 'motorcycle-chain-set', 'Heavy-duty chain and sprocket set. Includes front and rear sprockets.', 'Complete chain and sprocket kit', 899.00, 799.00, 40, 'CHN-SET', 4.5, 88, 195, 0, 'active'),
(2, 10, NULL, 'Motorcycle Tire', 'motorcycle-tire', 'Durable tubeless motorcycle tire with excellent wet and dry grip.', 'High-grip tubeless tire', 1899.00, 1699.00, 35, 'TIR-MT', 4.7, 210, 340, 1, 'active'),
(2, 9, NULL, 'Motorcycle Battery', 'motorcycle-battery', 'Maintenance-free sealed lead-acid battery for most 125-150cc bikes.', '12V sealed motorcycle battery', 1299.00, NULL, 28, 'BAT-12V', 4.4, 95, 180, 0, 'active'),
(2, 10, NULL, 'Motorcycle Phone Holder', 'motorcycle-phone-holder', 'Universal handlebar phone mount with strong grip.', 'Secure handlebar phone mount', 299.00, 249.00, 150, 'PHN-HLD', 4.2, 320, 890, 0, 'active'),
(2, 8, NULL, 'Motorcycle Tail Light', 'motorcycle-tail-light', 'LED tail light with integrated brake light and turn signals.', 'Multi-function LED tail light', 399.00, NULL, 65, 'TL-LED', 4.3, 76, 145, 0, 'active'),
(2, 9, NULL, 'Motorcycle Engine Oil', 'motorcycle-engine-oil', 'Fully synthetic 10W-40 motorcycle engine oil. 1 liter bottle.', '1L fully synthetic engine oil', 450.00, 399.00, 200, 'OIL-10W40', 4.6, 450, 2100, 1, 'active'),
(2, 10, NULL, 'Motorcycle Handle Grip', 'motorcycle-handle-grip', 'Soft rubber handle grips with anti-slip pattern.', 'Pair of anti-slip handle grips', 199.00, NULL, 180, 'GRP-HDL', 4.1, 110, 520, 0, 'active'),
(2, 10, NULL, 'Motorcycle Horn', 'motorcycle-horn', 'Loud dual-tone electric horn. Easy installation.', 'High-decibel electric horn', 249.00, 199.00, 95, 'HRN-DUAL', 4.2, 85, 230, 0, 'active'),
(2, 10, NULL, 'Motorcycle Raincoat', 'motorcycle-raincoat', 'Waterproof motorcycle raincoat with reflective strips.', 'Waterproof riding raincoat', 599.00, 499.00, 70, 'RCT-WP', 4.4, 198, 410, 0, 'active'),
(2, 10, NULL, 'Motorcycle Top Box', 'motorcycle-top-box', '35L lockable top box with mounting hardware.', '35 liter lockable storage box', 1499.00, 1349.00, 25, 'BOX-TOP35', 4.5, 67, 120, 1, 'active'),
(2, 9, NULL, 'Motorcycle Disc Brake', 'motorcycle-disc-brake', 'High-performance stainless steel brake disc.', 'Stainless steel brake rotor', 999.00, 899.00, 30, 'DSC-BRK', 4.6, 54, 95, 0, 'active');

INSERT INTO `product_images` (`product_id`, `image_path`, `is_primary`, `sort_order`)
SELECT id, CONCAT('uploads/products/product-', id, '.jpg'), 1, 0 FROM products;

INSERT INTO `coupons` (`code`, `type`, `value`, `min_order`, `max_discount`, `usage_limit`, `status`) VALUES
('WELCOME10', 'percentage', 10.00, 500.00, 300.00, 1000, 'active'),
('FREESHIP', 'fixed', 50.00, 1000.00, NULL, 500, 'active');
