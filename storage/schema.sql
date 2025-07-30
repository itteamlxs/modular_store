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
-- 5. SISTEMA DE AUDITORIA DE BASES DE DATOS Y LOGS DE USUARIO
-- ============================================================================

-- 1. Crear tabla de auditoría
CREATE TABLE audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50),
    record_id INT,
    action ENUM('INSERT','UPDATE','DELETE','LOGIN','LOGOUT'),
    old_values JSON,
    new_values JSON,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_user_action (user_id, action),
    INDEX idx_created (created_at)
);

-- 2. Procedimiento para login/logout
DELIMITER //
CREATE PROCEDURE log_user_action(
    IN p_action ENUM('LOGIN','LOGOUT'),
    IN p_user_id INT,
    IN p_ip VARCHAR(45),
    IN p_user_agent TEXT
)
BEGIN
    INSERT INTO audit_log (action, user_id, ip_address, user_agent)
    VALUES (p_action, p_user_id, p_ip, p_user_agent);
END //
DELIMITER ;

-- 3. Triggers para tabla PRODUCTS
DELIMITER //
CREATE TRIGGER products_after_insert
AFTER INSERT ON products
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values)
    VALUES ('products', NEW.id, 'INSERT', 
        JSON_OBJECT(
            'id', NEW.id,
            'category_id', NEW.category_id,
            'name', NEW.name,
            'price', NEW.price,
            'stock', NEW.stock,
            'image_url', NEW.image_url
        )
    );
END //

CREATE TRIGGER products_after_update
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, new_values)
    VALUES ('products', NEW.id, 'UPDATE',
        JSON_OBJECT(
            'id', OLD.id,
            'category_id', OLD.category_id,
            'name', OLD.name,
            'price', OLD.price,
            'stock', OLD.stock,
            'image_url', OLD.image_url
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'category_id', NEW.category_id,
            'name', NEW.name,
            'price', NEW.price,
            'stock', NEW.stock,
            'image_url', NEW.image_url
        )
    );
END //

CREATE TRIGGER products_after_delete
AFTER DELETE ON products
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values)
    VALUES ('products', OLD.id, 'DELETE',
        JSON_OBJECT(
            'id', OLD.id,
            'category_id', OLD.category_id,
            'name', OLD.name,
            'price', OLD.price,
            'stock', OLD.stock,
            'image_url', OLD.image_url
        )
    );
END //
DELIMITER ;

-- 4. Triggers para tabla CATEGORIES
DELIMITER //
CREATE TRIGGER categories_after_insert
AFTER INSERT ON categories
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values)
    VALUES ('categories', NEW.id, 'INSERT', 
        JSON_OBJECT(
            'id', NEW.id,
            'name', NEW.name,
            'description', NEW.description
        )
    );
END //

CREATE TRIGGER categories_after_update
AFTER UPDATE ON categories
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, new_values)
    VALUES ('categories', NEW.id, 'UPDATE',
        JSON_OBJECT(
            'id', OLD.id,
            'name', OLD.name,
            'description', OLD.description
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'name', NEW.name,
            'description', NEW.description
        )
    );
END //

CREATE TRIGGER categories_after_delete
AFTER DELETE ON categories
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values)
    VALUES ('categories', OLD.id, 'DELETE',
        JSON_OBJECT(
            'id', OLD.id,
            'name', OLD.name,
            'description', OLD.description
        )
    );
END //
DELIMITER ;

-- 5. Triggers para tabla USERS
DELIMITER //
CREATE TRIGGER users_after_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values)
    VALUES ('users', NEW.id, 'INSERT', 
        JSON_OBJECT(
            'id', NEW.id,
            'email', NEW.email,
            'is_admin', NEW.is_admin
            -- Intencionalmente no guardamos password_hash por seguridad
        )
    );
END //

CREATE TRIGGER users_after_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, new_values)
    VALUES ('users', NEW.id, 'UPDATE',
        JSON_OBJECT(
            'id', OLD.id,
            'email', OLD.email,
            'is_admin', OLD.is_admin
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'email', NEW.email,
            'is_admin', NEW.is_admin
        )
    );
END //

CREATE TRIGGER users_after_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values)
    VALUES ('users', OLD.id, 'DELETE',
        JSON_OBJECT(
            'id', OLD.id,
            'email', OLD.email,
            'is_admin', OLD.is_admin
        )
    );
END //
DELIMITER ;

-- 6. Triggers para tabla ORDERS
DELIMITER //
CREATE TRIGGER orders_after_insert
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values)
    VALUES ('orders', NEW.id, 'INSERT', 
        JSON_OBJECT(
            'id', NEW.id,
            'user_id', NEW.user_id,
            'stripe_id', NEW.stripe_id,
            'total', NEW.total,
            'status', NEW.status,
            'shipping_name', NEW.shipping_name,
            'shipping_email', NEW.shipping_email
        )
    );
END //

CREATE TRIGGER orders_after_update
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, new_values)
    VALUES ('orders', NEW.id, 'UPDATE',
        JSON_OBJECT(
            'id', OLD.id,
            'user_id', OLD.user_id,
            'stripe_id', OLD.stripe_id,
            'total', OLD.total,
            'status', OLD.status,
            'shipping_name', OLD.shipping_name,
            'shipping_email', OLD.shipping_email
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'user_id', NEW.user_id,
            'stripe_id', NEW.stripe_id,
            'total', NEW.total,
            'status', NEW.status,
            'shipping_name', NEW.shipping_name,
            'shipping_email', NEW.shipping_email
        )
    );
END //

CREATE TRIGGER orders_after_delete
AFTER DELETE ON orders
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values)
    VALUES ('orders', OLD.id, 'DELETE',
        JSON_OBJECT(
            'id', OLD.id,
            'user_id', OLD.user_id,
            'stripe_id', OLD.stripe_id,
            'total', OLD.total,
            'status', OLD.status,
            'shipping_name', OLD.shipping_name,
            'shipping_email', OLD.shipping_email
        )
    );
END //
DELIMITER ;

-- 7. Triggers para tabla ORDER_ITEMS
DELIMITER //
CREATE TRIGGER order_items_after_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values)
    VALUES ('order_items', NEW.id, 'INSERT', 
        JSON_OBJECT(
            'id', NEW.id,
            'order_id', NEW.order_id,
            'product_id', NEW.product_id,
            'quantity', NEW.quantity,
            'price_each', NEW.price_each
        )
    );
END //

CREATE TRIGGER order_items_after_update
AFTER UPDATE ON order_items
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, new_values)
    VALUES ('order_items', NEW.id, 'UPDATE',
        JSON_OBJECT(
            'id', OLD.id,
            'order_id', OLD.order_id,
            'product_id', OLD.product_id,
            'quantity', OLD.quantity,
            'price_each', OLD.price_each
        ),
        JSON_OBJECT(
            'id', NEW.id,
            'order_id', NEW.order_id,
            'product_id', NEW.product_id,
            'quantity', NEW.quantity,
            'price_each', NEW.price_each
        )
    );
END //

CREATE TRIGGER order_items_after_delete
AFTER DELETE ON order_items
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values)
    VALUES ('order_items', OLD.id, 'DELETE',
        JSON_OBJECT(
            'id', OLD.id,
            'order_id', OLD.order_id,
            'product_id', OLD.product_id,
            'quantity', OLD.quantity,
            'price_each', OLD.price_each
        )
    );
END //
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