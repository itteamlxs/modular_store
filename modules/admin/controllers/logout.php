<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

session_destroy();
header('Location: /modular-store/modules/admin/controllers/login.php');
exit;