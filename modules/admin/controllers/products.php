<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $name = sanitize($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $imageUrl = sanitize($_POST['image_url'] ?? '');
        
        if ($name && $price > 0 && $stock >= 0 && $categoryId > 0) {
            $stmt = Database::conn()->prepare(
                "INSERT INTO products (name, price, stock, category_id, image_url) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $price, $stock, $categoryId, $imageUrl]);
            $message = 'Product created successfully';
            $action = 'list';
        } else {
            $message = 'All fields are required';
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $imageUrl = sanitize($_POST['image_url'] ?? '');
        
        if ($id && $name && $price > 0 && $stock >= 0 && $categoryId > 0) {
            $stmt = Database::conn()->prepare(
                "UPDATE products SET name=?, price=?, stock=?, category_id=?, image_url=? WHERE id=?"
            );
            $stmt->execute([$name, $price, $stock, $categoryId, $imageUrl, $id]);
            $message = 'Product updated successfully';
            $action = 'list';
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    Database::conn()->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    $message = 'Product deleted successfully';
    $action = 'list';
}

$products = Database::view('v_admin_products');
$categories = Database::view('categories');

if ($action === 'edit' && isset($_GET['id'])) {
    $editProduct = Database::view('v_admin_products', ['id' => (int)$_GET['id']])[0] ?? null;
    if (!$editProduct) {
        $action = 'list';
        $message = 'Product not found';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/admin/controllers/dashboard.php">← Admin Panel</a>
        <span class="navbar-brand">Manage Products</span>
    </div>
</nav>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if ($action === 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Products</h1>
            <a href="?action=create" class="btn btn-primary">Add Product</a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category']) ?></td>
                            <td>$<?= number_format((float)$product['price'], 2) ?></td>
                            <td><?= $product['stock'] ?></td>
                            <td>
                                <a href="?action=edit&id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="?action=delete&id=<?= $product['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    <?php elseif ($action === 'create' || $action === 'edit'): ?>
        <h1><?= $action === 'create' ? 'Add' : 'Edit' ?> Product</h1>
        
        <form method="post" class="row g-3" style="max-width: 600px;">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
            <?php endif; ?>
            
            <div class="col-12">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" 
                       value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select class="form-select" name="category_id" required>
                    <option value="">Select category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" 
                                <?= ($editProduct['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" name="price" 
                       value="<?= $editProduct['price'] ?? '' ?>" required>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Stock</label>
                <input type="number" class="form-control" name="stock" 
                       value="<?= $editProduct['stock'] ?? '' ?>" required>
            </div>
            
            <div class="col-12">
                <label class="form-label">Image URL</label>
                <input type="url" class="form-control" name="image_url" 
                       value="<?= htmlspecialchars($editProduct['image_url'] ?? '') ?>">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary"><?= $action === 'create' ? 'Create' : 'Update' ?></button>
                <a href="?" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>