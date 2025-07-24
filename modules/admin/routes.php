<?php
declare(strict_types=1);

$base = '/modular-store/modules/admin/controllers';

// Auth
$router->get($base . '/login',  fn() => require __DIR__ . '/controllers/login.php');
$router->post($base . '/login', fn() => require __DIR__ . '/controllers/login.php');
$router->get($base . '/logout', fn() => require __DIR__ . '/controllers/logout.php');

// Dashboard
$router->get($base . '/dashboard', fn() => (new AdminController)->dashboard());

// Products
$router->get($base . '/products',      fn() => (new AdminController)->products());
$router->post($base . '/product-save', fn() => (new AdminController)->productSave());
$router->post($base . '/product-delete', fn() => (new AdminController)->productDelete());

// Orders
$router->get($base . '/orders',       fn() => (new AdminController)->orders());
$router->post($base . '/order-update', fn() => (new AdminController)->orderUpdate());

// Users
$router->get($base . '/users',       fn() => (new AdminUserController)->users());
$router->post($base . '/user-save',   fn() => (new AdminUserController)->userSave());
$router->post($base . '/user-delete', fn() => (new AdminUserController)->userDelete());
$router->post($base . '/user-reset',  fn() => (new AdminUserController)->userReset());