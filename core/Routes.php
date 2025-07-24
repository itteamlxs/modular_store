<?php
declare(strict_types=1);

use core\Router;

$router = new Router();

/* ----------  PÚBLICO  ---------- */
$router->get('/', fn() => (new ProductController)->index());
$router->get('/catalog', fn() => (new ProductController)->index());

/* ----------  CART  ---------- */
$router->post('/cart/add',     fn() => (new CartAddController)->execute());
$router->get('/cart',          fn() => (new CartViewController)->execute());
$router->post('/cart/update',  fn() => (new CartUpdateController)->execute());
$router->post('/cart/remove',  fn() => (new CartRemoveController)->execute());
$router->get('/cart/empty',    fn() => (new CartEmptyController)->execute());

/* ----------  CHECKOUT  ---------- */
$router->get('/checkout',      fn() => (new CheckoutController)->index());
$router->get('/checkout/success', fn() => (new CheckoutSuccessController)->execute());

/* ----------  ADMIN  ---------- */
$router->get('/admin/login',   fn() => (new AdminLoginController)->show());
$router->post('/admin/login',  fn() => (new AdminLoginController)->doLogin());
$router->get('/admin/logout',  fn() => (new AdminLogoutController)->logout());
$router->get('/admin',         fn() => (new AdminDashboardController)->index());
$router->get('/admin/users/new', fn() => (new AdminUserController)->createForm());
$router->post('/admin/users',  fn() => (new AdminUserController)->store());

return $router;