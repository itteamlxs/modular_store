<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../core/Database.php';

$pwd = password_hash('admin123', PASSWORD_DEFAULT);
Database::conn()->exec(
    "INSERT INTO users (email, password_hash, is_admin) 
     VALUES ('admin@store.com', '$pwd', 1) 
     ON DUPLICATE KEY UPDATE password_hash='$pwd'"
);
echo "Admin creado: admin@store.com / admin123\n";