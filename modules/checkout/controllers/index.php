<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';   // ← AÑADIDO

if (empty($_SESSION['cart'] ?? [])) {
    header('Location: /modular-store/modules/cart/controllers/view.php');
    exit;
}

require_once __DIR__ . '/../views/checkout.php';