<?php
declare(strict_types=1);

$base = '/modular-store/public';

// Autenticación
$router->get($base . '/admin/login',     fn() => (new AdminLoginController)->show());
$router->post($base . '/admin/login',    fn() => (new AdminLoginController)->doLogin());
$router->get($base . '/admin/logout',    fn() => (new AdminLogoutController)->logout());

// Dashboard
$router->get($base . '/admin',           fn() => (new AdminController)->dashboard());

// Gestión de usuarios admin
$router->get($base . '/admin/users/new', fn() => (new AdminUserController)->createForm());
$router->post($base . '/admin/users',    fn() => (new AdminUserController)->store());

// Gestión de órdenes
$router->get($base . '/admin/orders',           fn() => (new AdminController)->orders());
$router->get($base . '/admin/orders/detail',    fn() => (new AdminController)->orderDetail());
$router->post($base . '/admin/orders/status',   fn() => (new AdminController)->updateOrderStatus());

// Gestión de productos
$router->get($base . '/admin/products',         fn() => (new AdminController)->products());
$router->get($base . '/admin/products/new',     fn() => (new AdminController)->createProduct());
$router->post($base . '/admin/products',        fn() => (new AdminController)->storeProduct());
$router->get($base . '/admin/products/edit',    fn() => (new AdminController)->editProduct());
$router->post($base . '/admin/products/update', fn() => (new AdminController)->updateProduct());
$router->post($base . '/admin/products/delete', fn() => (new AdminController)->deleteProduct());

// Gestión de categorías
$router->get($base . '/admin/categories',       fn() => (new AdminController)->categories());
$router->post($base . '/admin/categories',      fn() => (new AdminController)->storeCategory());
$router->post($base . '/admin/categories/delete', fn() => (new AdminController)->deleteCategory());