<?php
declare(strict_types=1);

$base = '/modular-store/public/admin';

// Autenticación
$router->get($base . '/login',     fn() => (new AdminLoginController)->show());
$router->post($base . '/login',    fn() => (new AdminLoginController)->doLogin());
$router->get($base . '/logout',    fn() => (new AdminLogoutController)->logout());

// Dashboard
$router->get($base,                fn() => (new AdminController)->dashboard());

// Gestión de usuarios admin
$router->get($base . '/users/new', fn() => (new AdminUserController)->createForm());
$router->post($base . '/users',    fn() => (new AdminUserController)->store());

// Gestión de órdenes
$router->get($base . '/orders',           fn() => (new AdminController)->orders());
$router->get($base . '/orders/detail',    fn() => (new AdminController)->orderDetail());
$router->post($base . '/orders/status',   fn() => (new AdminController)->updateOrderStatus());

// Gestión de productos
$router->get($base . '/products',         fn() => (new AdminController)->products());
$router->get($base . '/products/new',     fn() => (new AdminController)->createProduct());
$router->post($base . '/products',        fn() => (new AdminController)->storeProduct());
$router->get($base . '/products/edit',    fn() => (new AdminController)->editProduct());
$router->post($base . '/products/update', fn() => (new AdminController)->updateProduct());
$router->post($base . '/products/delete', fn() => (new AdminController)->deleteProduct());

// Gestión de categorías
$router->get($base . '/categories',       fn() => (new AdminController)->categories());
$router->post($base . '/categories',      fn() => (new AdminController)->storeCategory());
$router->post($base . '/categories/delete', fn() => (new AdminController)->deleteCategory());