<?php
declare(strict_types=1);

$router->get('/admin/login',     fn() => (new AdminLoginController)->show());
$router->post('/admin/login',    fn() => (new AdminLoginController)->doLogin());
$router->get('/admin/logout',    fn() => (new AdminLogoutController)->logout());
$router->get('/admin',           fn() => (new AdminDashboardController)->index());
$router->get('/admin/users/new', fn() => (new AdminUserController)->createForm());
$router->post('/admin/users',    fn() => (new AdminUserController)->store());