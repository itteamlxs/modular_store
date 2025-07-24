<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';

// Cargar controladores de todos los módulos
foreach (glob(__DIR__ . '/../modules/*/controllers/*.php') as $file) {
    require_once $file;
}

$router = require __DIR__ . '/../core/Routes.php';
$router->dispatch();