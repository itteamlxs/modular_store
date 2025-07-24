<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';

// Include all controllers
require_once __DIR__ . '/../modules/catalog/controllers/ProductController.php';
require_once __DIR__ . '/../modules/admin/controllers/login.php';
require_once __DIR__ . '/../modules/admin/controllers/logout.php';
require_once __DIR__ . '/../modules/admin/controllers/AdminController.php';
require_once __DIR__ . '/../modules/admin/controllers/AdminUserController.php';

$router = new Router();

// Include all routes
require_once __DIR__ . '/../modules/catalog/routes.php';
require_once __DIR__ . '/../modules/admin/routes.php';

$router->dispatch();
?>