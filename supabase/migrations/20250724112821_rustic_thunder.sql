@@ .. @@
 CREATE TABLE IF NOT EXISTS users (
     id         INT AUTO_INCREMENT PRIMARY KEY,
     email      VARCHAR(255) NOT NULL UNIQUE,
-    password   CHAR(255) NOT NULL,
+    password_hash CHAR(255) NOT NULL,
     is_admin   TINYINT(1) DEFAULT 0,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP
 );
@@ .. @@
 ADD COLUMN latitude           DECIMAL(10, 8),
 ADD COLUMN longitude          DECIMAL(11, 8);

--- Ampliamos bases de tabla user
-
-ALTER TABLE users ADD COLUMN password_hash CHAR(255) NOT NULL;