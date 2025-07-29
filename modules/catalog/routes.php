<?php
declare(strict_types=1);

$base = '/modular-store/public';

$router->get($base,          fn() => (new ProductController)->index());
$router->get($base . '/',    fn() => (new ProductController)->index());
$router->get($base . '/catalog', fn() => (new ProductController)->index());
$router->get($base . '/api/search', fn() => (new SearchController)->search());