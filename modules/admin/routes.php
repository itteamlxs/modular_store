<?php
declare(strict_types=1);

$base = '/modular-store/modules/admin';

$router->get($base . '/login',     fn() => require __DIR__ . '/controllers/login.php');
$router->post($base . '/login',    fn() => require __DIR__ . '/controllers/login.php');
$router->get($base . '/logout',    fn() => require __DIR__ . '/controllers/logout.php');
$router->get($base . '/dashboard', fn() => require __DIR__ . '/controllers/dashboard.php');
$router->get($base . '/products',  fn() => require __DIR__ . '/controllers/products.php');
$router->post($base . '/products', fn() => require __DIR__ . '/controllers/products.php');
$router->get($base . '/orders',    fn() => require __DIR__ . '/controllers/orders.php');
$router->post($base . '/orders',   fn() => require __DIR__ . '/controllers/orders.php');
$router->get($base . '/envios',    fn() => require __DIR__ . '/controllers/envios.php');
$router->post($base . '/envios',   fn() => require __DIR__ . '/controllers/envios.php');
$router->get($base . '/users',     fn() => require __DIR__ . '/controllers/users.php');
$router->post($base . '/users',    fn() => require __DIR__ . '/controllers/users.php');
$router->get($base . '/reports', fn() => require __DIR__ . '/controllers/reports.php');
$router->post($base . '/reports', fn() => require __DIR__ . '/controllers/reports.php');