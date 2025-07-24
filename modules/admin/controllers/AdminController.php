<?php
declare(strict_types=1);

class AdminController
{
    private function checkAuth(): void
    {
        if (!($_SESSION['is_admin'] ?? false)) {
            header('Location: /modular-store/modules/admin/controllers/login.php');
            exit;
        }
    }

    public function dashboard(): void
    {
        $this->checkAuth();
        $stats = [
            'products' => Database::conn()->query("SELECT COUNT(*) as c FROM products")->fetch()['c'],
            'orders' => Database::conn()->query("SELECT COUNT(*) as c FROM orders")->fetch()['c'],
            'users' => Database::conn()->query("SELECT COUNT(*) as c FROM users")->fetch()['c'],
            'revenue' => Database::conn()->query("SELECT SUM(total) as c FROM orders WHERE status='paid'")->fetch()['c'] ?? 0
        ];
        require __DIR__ . '/../views/dashboard.php';
    }

    public function products(): void
    {
        $this->checkAuth();
        $products = Database::view('v_admin_products');
        $categories = Database::view('categories');
        require __DIR__ . '/../views/products.php';
    }

    public function orders(): void
    {
        $this->checkAuth();
        $orders = Database::view('v_admin_orders');
        require __DIR__ . '/../views/orders.php';
    }

    public function productSave(): void
    {
        $this->checkAuth();
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $image_url = trim($_POST['image_url'] ?? '');

        if ($id > 0) {
            $stmt = Database::conn()->prepare("UPDATE products SET name=?, price=?, stock=?, category_id=?, image_url=? WHERE id=?");
            $stmt->execute([$name, $price, $stock, $category_id, $image_url, $id]);
        } else {
            $stmt = Database::conn()->prepare("INSERT INTO products (name, price, stock, category_id, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $stock, $category_id, $image_url]);
        }
        header('Location: /modular-store/modules/admin/controllers/products.php');
        exit;
    }

    public function productDelete(): void
    {
        $this->checkAuth();
        $id = (int)($_POST['id'] ?? 0);
        Database::conn()->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
        header('Location: /modular-store/modules/admin/controllers/products.php');
        exit;
    }

    public function orderUpdate(): void
    {
        $this->checkAuth();
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        Database::conn()->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $id]);
        header('Location: /modular-store/modules/admin/controllers/orders.php');
        exit;
    }
}