-- ============================================================================
-- MODULAR STORE DATABASE SCHEMA
-- Complete database setup with tables, views, and triggers
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================================
-- 1. TABLES CREATION
-- ============================================================================

-- Categories table
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `password_hash` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Products table
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0),
  `stock` int(11) NOT NULL CHECK (`stock` >= 0),
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Orders table
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `stripe_id` varchar(255) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL CHECK (`total` >= 0),
  `status` enum('pending','paid','shipped','cancelled') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `shipping_name` varchar(120) NOT NULL,
  `shipping_email` varchar(255) NOT NULL,
  `shipping_address` text NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `card_last4` char(4) DEFAULT NULL,
  `card_brand` varchar(20) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stripe_id` (`stripe_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Order items table
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `price_each` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Shipments table
CREATE TABLE `shipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','shipped','cancelled','returned') DEFAULT 'pending',
  `tracking_number` varchar(100) DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_order_shipment` (`order_id`),
  CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- 2. VIEWS CREATION
-- ============================================================================

-- Basic categories view
CREATE VIEW `v_categories` AS 
SELECT `id`, `name`, `description` 
FROM `categories` 
ORDER BY `name` ASC;

-- Basic users view (without password)
CREATE VIEW `v_users` AS 
SELECT `id`, `email`, `is_admin`, `created_at` 
FROM `users`;

-- Admin users view (with password for auth)
CREATE VIEW `v_admin_users` AS 
SELECT `id`, `email`, `password_hash`, `is_admin`, `created_at` 
FROM `users` 
ORDER BY `created_at` DESC;

-- Products view with category info (only in-stock)
CREATE VIEW `v_products` AS 
SELECT p.`id`, p.`name`, p.`price`, p.`stock`, p.`image_url`, c.`name` as `category` 
FROM `products` p 
JOIN `categories` c ON p.`category_id` = c.`id` 
WHERE p.`stock` > 0;

-- Admin products view (all products)
CREATE VIEW `v_admin_products` AS 
SELECT p.`id`, p.`name`, p.`price`, p.`stock`, p.`image_url`, p.`created_at`, p.`updated_at`, 
       c.`id` as `category_id`, c.`name` as `category` 
FROM `products` p 
JOIN `categories` c ON p.`category_id` = c.`id`;

-- Orders view with items as JSON
CREATE VIEW `v_orders` AS 
SELECT o.`id`, o.`user_id`, o.`stripe_id`, o.`total`, o.`status`, o.`created_at`,
       CONCAT('[',
         GROUP_CONCAT(
           CONCAT('{"product_id":', oi.`product_id`, 
                  ',"product_name":"', REPLACE(p.`name`, '"', '\\"'), 
                  '","quantity":', oi.`quantity`, 
                  ',"price_each":', oi.`price_each`, '}') 
           SEPARATOR ','
         ),
       ']') as `items`
FROM `orders` o
LEFT JOIN `order_items` oi ON oi.`order_id` = o.`id`
LEFT JOIN `products` p ON p.`id` = oi.`product_id`
GROUP BY o.`id`;

-- Admin orders view with item count
CREATE VIEW `v_admin_orders` AS 
SELECT o.`id`, o.`user_id`, o.`stripe_id`, o.`total`, o.`status`, o.`created_at`,
       o.`shipping_name`, o.`shipping_email`, o.`shipping_address`, o.`phone`,
       o.`card_last4`, o.`card_brand`, COUNT(oi.`id`) as `item_count`
FROM `orders` o
LEFT JOIN `order_items` oi ON oi.`order_id` = o.`id`
GROUP BY o.`id`
ORDER BY o.`created_at` DESC;

-- Shipments view (only paid orders)
CREATE VIEW `v_shipments` AS 
SELECT s.`id`, s.`order_id`, s.`status` as `shipment_status`, s.`tracking_number`, 
       s.`shipped_at`, s.`notes`, o.`shipping_name`, o.`shipping_email`, 
       o.`shipping_address`, o.`total`, o.`created_at` as `order_date`,
       COUNT(oi.`id`) as `item_count`
FROM `shipments` s
JOIN `orders` o ON o.`id` = s.`order_id`
LEFT JOIN `order_items` oi ON oi.`order_id` = o.`id`
WHERE o.`status` = 'paid'
GROUP BY s.`id`
ORDER BY s.`status` ASC, s.`created_at` ASC;

-- Sales reports view
CREATE VIEW `v_reports_sales` AS 
SELECT o.`id` as `order_id`, o.`stripe_id`, o.`shipping_name` as `customer_name`,
       o.`shipping_email` as `customer_email`, o.`total` as `order_total`,
       o.`status` as `order_status`, o.`created_at` as `order_date`,
       o.`card_last4`, o.`card_brand`, COUNT(oi.`id`) as `total_items`,
       GROUP_CONCAT(CONCAT(p.`name`, ' (', oi.`quantity`, 'x $', oi.`price_each`, ')') SEPARATOR ', ') as `products`
FROM `orders` o
LEFT JOIN `order_items` oi ON oi.`order_id` = o.`id`
LEFT JOIN `products` p ON p.`id` = oi.`product_id`
GROUP BY o.`id`;

-- Shipments reports view
CREATE VIEW `v_reports_shipments` AS 
SELECT s.`id` as `shipment_id`, s.`order_id`, o.`shipping_name` as `customer_name`,
       o.`shipping_email` as `customer_email`, o.`shipping_address`, o.`phone`,
       o.`total` as `order_total`, s.`status` as `shipment_status`,
       s.`tracking_number`, s.`shipped_at`, s.`notes`,
       o.`created_at` as `order_date`, s.`created_at` as `shipment_created`,
       s.`updated_at` as `shipment_updated`, COUNT(oi.`id`) as `total_items`
FROM `shipments` s
JOIN `orders` o ON o.`id` = s.`order_id`
LEFT JOIN `order_items` oi ON oi.`order_id` = o.`id`
GROUP BY s.`id`;

-- Detailed reports view (order + shipment + product details)
CREATE VIEW `v_reports_detailed` AS 
SELECT o.`id` as `order_id`, o.`stripe_id`, o.`shipping_name` as `customer_name`,
       o.`shipping_email` as `customer_email`, o.`shipping_address`, o.`phone`,
       o.`total` as `order_total`, o.`status` as `order_status`, o.`created_at` as `order_date`,
       o.`card_last4`, o.`card_brand`, o.`ip_address`, o.`latitude`, o.`longitude`,
       p.`name` as `product_name`, c.`name` as `category_name`,
       oi.`quantity`, oi.`price_each`, (oi.`quantity` * oi.`price_each`) as `item_subtotal`,
       s.`status` as `shipment_status`, s.`tracking_number`, s.`shipped_at`, s.`notes` as `shipment_notes`
FROM `orders` o
LEFT JOIN `order_items` oi ON oi.`order_id` = o.`id`
LEFT JOIN `products` p ON p.`id` = oi.`product_id`
LEFT JOIN `categories` c ON c.`id` = p.`category_id`
LEFT JOIN `shipments` s ON s.`order_id` = o.`id`
ORDER BY o.`created_at` DESC, oi.`id` ASC;

-- ============================================================================
-- 3. TRIGGERS CREATION
-- ============================================================================

-- Trigger: Create shipment when order is paid (INSERT)
DELIMITER $$
DROP TRIGGER IF EXISTS create_shipment_on_paid_order$$
CREATE TRIGGER create_shipment_on_paid_order
    AFTER INSERT ON orders
    FOR EACH ROW
BEGIN
    -- Crear shipment automáticamente si el pedido está pagado
    IF NEW.status = 'paid' THEN
        INSERT INTO shipments (order_id, status, created_at, updated_at)
        VALUES (NEW.id, 'pending', NOW(), NOW());
    END IF;
END$$
DELIMITER ;

-- Trigger: Create shipment when order status changes to paid (UPDATE)
DELIMITER $$
DROP TRIGGER IF EXISTS update_shipment_on_order_change$$
CREATE TRIGGER update_shipment_on_order_change
    AFTER UPDATE ON orders
    FOR EACH ROW
BEGIN
    -- Si la orden cambia a 'paid' y no tiene shipment, crear uno
    IF NEW.status = 'paid' AND OLD.status != 'paid' THEN
        INSERT IGNORE INTO shipments (order_id, status, created_at, updated_at)
        VALUES (NEW.id, 'pending', NOW(), NOW());
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- 4. SAMPLE DATA INSERTION
-- ============================================================================

-- Insert categories
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Electronics', 'Gadgets & devices'),
(2, 'Books', 'Fiction & non-fiction');

-- Insert sample products
INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `stock`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'Wireless Mouse', 24.99, 50, 'https://placehold.co/600x400', NOW(), NOW()),
(2, 1, 'USB-C Cable', 9.90, 120, 'https://placehold.co/600x400', NOW(), NOW()),
(3, 2, 'Clean Code', 39.50, 30, 'https://placehold.co/600x400', NOW(), NOW()),
(4, 1, 'iPhone 15', 999.99, 25, 'https://placehold.co/600x400', NOW(), NOW()),
(5, 1, 'Bluetooth Headphones', 79.99, 40, 'https://placehold.co/600x400', NOW(), NOW());

-- Insert admin user (password: admin123)
INSERT INTO `users` (`id`, `email`, `is_admin`, `created_at`, `password_hash`) VALUES
(1, 'admin@store.com', 1, NOW(), '$2y$10$.FnXRvhUDrl.7Z./SzWNveL4tQUsxio.IGoVXIzsK0RyDkT7Tp/QC');

-- Reset AUTO_INCREMENT counters
ALTER TABLE `categories` AUTO_INCREMENT = 3;
ALTER TABLE `users` AUTO_INCREMENT = 2;
ALTER TABLE `products` AUTO_INCREMENT = 6;
ALTER TABLE `orders` AUTO_INCREMENT = 1;
ALTER TABLE `order_items` AUTO_INCREMENT = 1;
ALTER TABLE `shipments` AUTO_INCREMENT = 1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================================================
-- INSTALLATION COMPLETE
-- ============================================================================
-- Database: modules_store
-- Tables: 6 (categories, users, products, orders, order_items, shipments)
-- Views: 10 (all necessary views for admin and frontend)
-- Triggers: 2 (automatic shipment creation for paid orders)
-- Sample Data: Categories, products, and admin user included
-- ============================================================================