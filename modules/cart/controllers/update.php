<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

session_start();

$productId = (int)($_POST['product_id'] ?? 0);
$delta     = (int)($_POST['delta'] ?? 0);

if (!isset($_SESSION['cart'][$productId])) {
    header('Location: /modular-store/modules/cart/controllers/view.php');
    exit;
}

$_SESSION['cart'][$productId] += $delta;
if ($_SESSION['cart'][$productId] <= 0) {
    unset($_SESSION['cart'][$productId]);
}

header('Location: /modular-store/modules/cart/controllers/view.php');
exit;