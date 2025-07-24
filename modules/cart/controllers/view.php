<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';   // ← AÑADIDO

$items = [];
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        $p = Database::view('v_products', ['id' => $id])[0] ?? null;
        if ($p) {
            $items[] = array_merge($p, ['qty' => $qty]);
        }
    }
}

require_once __DIR__ . '/../views/widget.php';