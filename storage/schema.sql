CREATE DATABASE IF NOT EXISTS modules_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE modules_store;

-- 1NF: atomic columns
CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name        VARCHAR(150) NOT NULL,
    price       DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    stock       INT NOT NULL CHECK (stock >= 0),
    image_url   VARCHAR(255),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   CHAR(255) NOT NULL,
    is_admin   TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT,
    stripe_id  VARCHAR(255) UNIQUE,
    total      DECIMAL(10,2) NOT NULL CHECK (total >= 0),
    status     ENUM('pending','paid','shipped','cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    product_id INT NOT NULL,
    quantity   INT NOT NULL CHECK (quantity > 0),
    price_each DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 2NF: no partial dependencies
-- 3NF: no transitive dependencies (already satisfied)

-- Views = ONLY public interface
CREATE OR REPLACE VIEW v_products AS
SELECT p.id, p.name, p.price, p.stock, p.image_url, c.name AS category
FROM products p JOIN categories c ON p.category_id = c.id
WHERE p.stock > 0;

CREATE OR REPLACE VIEW v_orders AS
SELECT o.id, o.user_id, o.stripe_id, o.total, o.status, o.created_at,
       CONCAT('[', GROUP_CONCAT(
           CONCAT(
               '{"product_id":', oi.product_id,
               ',"product_name":"', REPLACE(p.name, '"', '\\"'),
               '","quantity":', oi.quantity,
               ',"price_each":', oi.price_each, '}'
           )
       ), ']') AS items
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
LEFT JOIN products p ON p.id = oi.product_id
GROUP BY o.id;

CREATE OR REPLACE VIEW v_users AS
SELECT id, email, is_admin, created_at FROM users;

-- Ampliamos tabla Orders para obtener datos de compra

ALTER TABLE orders
ADD COLUMN shipping_name      VARCHAR(120)  NOT NULL,
ADD COLUMN shipping_email     VARCHAR(255)  NOT NULL,
ADD COLUMN shipping_address   TEXT          NOT NULL,
ADD COLUMN phone              VARCHAR(30),
ADD COLUMN card_last4         CHAR(4),
ADD COLUMN card_brand         VARCHAR(20),
ADD COLUMN ip_address         VARCHAR(45),
ADD COLUMN latitude           DECIMAL(10, 8),
ADD COLUMN longitude          DECIMAL(11, 8);

-- Ampliamos bases de tabla user

ALTER TABLE users ADD COLUMN password_hash CHAR(255) NOT NULL;

-- Vistas del modulo admin

-- Vistas adicionales para el módulo admin
USE modules_store;

-- Vista completa de productos para admin (incluye sin stock)
CREATE OR REPLACE VIEW v_admin_products AS
SELECT p.id, p.name, p.price, p.stock, p.image_url, p.created_at, p.updated_at,
       c.id as category_id, c.name AS category
FROM products p JOIN categories c ON p.category_id = c.id;

-- Vista de órdenes detallada para admin
CREATE OR REPLACE VIEW v_admin_orders AS
SELECT o.id, o.user_id, o.stripe_id, o.total, o.status, o.created_at,
       o.shipping_name, o.shipping_email, o.shipping_address, o.phone,
       o.card_last4, o.card_brand,
       COUNT(oi.id) as item_count
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
GROUP BY o.id
ORDER BY o.created_at DESC;

-- Vista de usuarios para admin (incluye password_hash para validaciones)
CREATE OR REPLACE VIEW v_admin_users AS
SELECT id, email, password_hash, is_admin, created_at 
FROM users 
ORDER BY created_at DESC;

-- Sales Report View
CREATE OR REPLACE VIEW v_reports_sales AS
SELECT 
    o.id as order_id,
    o.stripe_id,
    o.shipping_name as customer_name,
    o.shipping_email as customer_email,
    o.total as order_total,
    o.status as order_status,
    o.created_at as order_date,
    o.card_last4,
    o.card_brand,
    COUNT(oi.id) as total_items,
    GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, 'x $', oi.price_each, ')') SEPARATOR ', ') as products
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
LEFT JOIN products p ON p.id = oi.product_id
GROUP BY o.id;

-- Shipments Report View
CREATE OR REPLACE VIEW v_reports_shipments AS
SELECT 
    s.id as shipment_id,
    s.order_id,
    o.shipping_name as customer_name,
    o.shipping_email as customer_email,
    o.shipping_address,
    o.phone,
    o.total as order_total,
    s.status as shipment_status,
    s.tracking_number,
    s.shipped_at,
    s.notes,
    o.created_at as order_date,
    s.created_at as shipment_created,
    s.updated_at as shipment_updated,
    COUNT(oi.id) as total_items
FROM shipments s
JOIN orders o ON o.id = s.order_id
LEFT JOIN order_items oi ON oi.order_id = o.id
GROUP BY s.id;

-- Detailed Report View
CREATE OR REPLACE VIEW v_reports_detailed AS
SELECT 
    o.id as order_id,
    o.stripe_id,
    o.shipping_name as customer_name,
    o.shipping_email as customer_email,
    o.shipping_address,
    o.phone,
    o.total as order_total,
    o.status as order_status,
    o.created_at as order_date,
    o.card_last4,
    o.card_brand,
    o.ip_address,
    o.latitude,
    o.longitude,
    p.name as product_name,
    c.name as category_name,
    oi.quantity,
    oi.price_each,
    (oi.quantity * oi.price_each) as item_subtotal,
    s.status as shipment_status,
    s.tracking_number,
    s.shipped_at,
    s.notes as shipment_notes
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
LEFT JOIN products p ON p.id = oi.product_id
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN shipments s ON s.order_id = o.id
ORDER BY o.created_at DESC, oi.id;