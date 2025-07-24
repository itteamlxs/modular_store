<?php
declare(strict_types=1);

$router->post('/email/send', [SendController::class, 'send']);