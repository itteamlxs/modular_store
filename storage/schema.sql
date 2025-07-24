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

--Ampliamos tabla Orders para obtener datos de compra

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