<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

$_SESSION['cart'] = [];
header('Location: /modular-store/modules/cart/controllers/view.php');
exit;