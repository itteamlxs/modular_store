<?php
declare(strict_types=1);

class AdminController
{
    public function dashboard(): void
    {
        $this->requireAuth();
        
        // Obtener estadísticas básicas
        $stats = $this->getStats();
        $recentOrders = Database::view('v_orders');
        
        require __DIR__ . '/../views/dashboard.php';
    }

    public function orders(): void
    {
        $this->requireAuth();
        
        $orders = Database::conn()->query("
            SELECT o.id, o.shipping_name, o.shipping_email, o.total, 
                   o.status, o.created_at, o.stripe_id,
                   COUNT(oi.id) as item_count
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            GROUP BY o.id 
            ORDER BY o.created_at DESC
        ")->fetchAll();
        
        require __DIR__ . '/../views/orders.php';
    }

    public function orderDetail(): void
    {
        $this->requireAuth();
        
        $orderId = (int)($_GET['id'] ?? 0);
        if (!$orderId) {
            header('Location: /modular-store/public/admin/orders');
            exit;
        }

        $order = Database::conn()->prepare("
            SELECT * FROM orders WHERE id = ?
        ");
        $order->execute([$orderId]);
        $orderData = $order->fetch();

        if (!$orderData) {
            header('Location: /modular-store/public/admin/orders');
            exit;
        }

        $items = Database::conn()->prepare("
            SELECT oi.*, p.name, p.image_url 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $items->execute([$orderId]);
        $orderItems = $items->fetchAll();

        require __DIR__ . '/../views/order_detail.php';
    }

    public function products(): void
    {
        $this->requireAuth();
        
        $products = Database::conn()->query("
            SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC
        ")->fetchAll();
        
        require __DIR__ . '/../views/products.php';
    }

    public function createProduct(): void
    {
        $this->requireAuth();
        
        $categories = Database::conn()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
        require __DIR__ . '/../views/product_form.php';
    }

    public function storeProduct(): void
    {
        $this->requireAuth();

        $name = sanitize($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $imageUrl = sanitize($_POST['image_url'] ?? '');

        if (!$name || !$categoryId || $price <= 0 || $stock < 0) {
            header('Location: /modular-store/public/admin/products/new?error=validation');
            exit;
        }

        $stmt = Database::conn()->prepare("
            INSERT INTO products (name, category_id, price, stock, image_url) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $categoryId, $price, $stock, $imageUrl]);

        header('Location: /modular-store/public/admin/products');
        exit;
    }

    public function editProduct(): void
    {
        $this->requireAuth();
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: /modular-store/public/admin/products');
            exit;
        }

        $product = Database::conn()->prepare("SELECT * FROM products WHERE id = ?");
        $product->execute([$id]);
        $productData = $product->fetch();

        if (!$productData) {
            header('Location: /modular-store/public/admin/products');
            exit;
        }

        $categories = Database::conn()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
        require __DIR__ . '/../views/product_form.php';
    }

    public function updateProduct(): void
    {
        $this->requireAuth();

        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $imageUrl = sanitize($_POST['image_url'] ?? '');

        if (!$id || !$name || !$categoryId || $price <= 0 || $stock < 0) {
            header('Location: /modular-store/public/admin/products/edit?id=' . $id . '&error=validation');
            exit;
        }

        $stmt = Database::conn()->prepare("
            UPDATE products 
            SET name = ?, category_id = ?, price = ?, stock = ?, image_url = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $categoryId, $price, $stock, $imageUrl, $id]);

        header('Location: /modular-store/public/admin/products');
        exit;
    }

    public function deleteProduct(): void
    {
        $this->requireAuth();

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = Database::conn()->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
        }

        header('Location: /modular-store/public/admin/products');
        exit;
    }

    public function categories(): void
    {
        $this->requireAuth();
        
        $categories = Database::conn()->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id 
            ORDER BY c.name
        ")->fetchAll();
        
        require __DIR__ . '/../views/categories.php';
    }

    public function storeCategory(): void
    {
        $this->requireAuth();

        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');

        if (!$name) {
            header('Location: /modular-store/public/admin/categories?error=name_required');
            exit;
        }

        $stmt = Database::conn()->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);

        header('Location: /modular-store/public/admin/categories');
        exit;
    }

    public function deleteCategory(): void
    {
        $this->requireAuth();

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            // Verificar que no tenga productos
            $count = Database::conn()->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
            $count->execute([$id]);
            
            if ($count->fetchColumn() == 0) {
                $stmt = Database::conn()->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
            }
        }

        header('Location: /modular-store/public/admin/categories');
        exit;
    }

    public function updateOrderStatus(): void
    {
        $this->requireAuth();

        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        $validStatuses = ['pending', 'paid', 'shipped', 'cancelled'];
        if ($orderId && in_array($status, $validStatuses)) {
            $stmt = Database::conn()->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);
        }

        header('Location: /modular-store/public/admin/orders');
        exit;
    }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /modular-store/public/admin/login');
            exit;
        }
    }

    private function getStats(): array
    {
        $pdo = Database::conn();
        
        $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $totalRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'paid'")->fetchColumn();
        $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5")->fetchColumn();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_products' => $totalProducts,
            'low_stock' => $lowStock
        ];
    }
}