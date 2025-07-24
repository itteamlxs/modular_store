<?php
declare(strict_types=1);

class ProductController
{
    public function index(): void
    {
        $products = Database::view('v_products');
        require __DIR__ . '/../views/list.php';
    }
}