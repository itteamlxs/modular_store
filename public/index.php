<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';

/* Cargamos el controlador del catálogo */
require_once __DIR__ . '/../modules/catalog/controllers/ProductController.php';

$router = new Router();

/* Cargamos las rutas del catálogo */
require_once __DIR__ . '/../modules/catalog/routes.php';

$router->dispatch();