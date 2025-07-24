<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';   // ← AÑADIDO

$productId = (int)($_POST['product_id'] ?? 0);
if ($productId <= 0) {
    die('Invalid product');
}

$product = Database::view('v_products', ['id' => $productId])[0] ?? null;
if (!$product) {
    die('Product not found');
}

$_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;

header('Location: /modular-store/modules/cart/controllers/view.php');
exit;