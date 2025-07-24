<?php
declare(strict_types=1);

$router->get('/admin/login',  fn() => (new AdminLoginController)->show());
$router->post('/admin/login', fn() => (new AdminLoginController)->doLogin());
$router->get('/admin',        fn() => (new AdminDashboardController)->index());
$router->get('/admin/logout', fn() => (new AdminLogoutController)->logout());