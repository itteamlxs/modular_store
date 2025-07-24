<?php if (!isset($_SESSION['admin_id'])) { die('Access denied'); } ?>
<?php $isEdit = isset($productData); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/public/admin">
            <i class="fas fa-cogs"></i> Admin Panel
        </a>
        <div class="navbar-nav">
            <a class="nav-link text-light" href="/modular-store/public/admin/logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="card">
                <div class="card-body p-2">
                    <nav class="nav flex-column">
                        <a class="nav-link" href="/modular-store/public/admin">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="/modular-store/public/admin/orders">
                            <i class="fas fa-shopping-bag"></i> Orders
                        </a>
                        <a class="nav-link active" href="/modular-store/public/admin/products">
                            <i class="fas fa-box"></i> Products
                        </a>
                        <a class="nav-link" href="/modular-store/public/admin/categories">
                            <i class="fas fa-tags"></i> Categories
                        </a>
                        <a class="nav-link" href="/modular-store/public/admin/users/new">
                            <i class="fas fa-user-plus"></i> Add Admin
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3"><?= $isEdit ? 'Edit' : 'Add' ?> Product</h1>
                <a href="/modular-store/public/admin/products" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger">
                                    <?php if ($_GET['error'] === 'validation'): ?>
                                        Please fill all required fields correctly.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <form action="<?= $isEdit ? '/modular-store/public/admin/products/update' : '/modular-store/public/admin/products' ?>" 
                                  method="post">
                                <?php if ($isEdit): ?>
                                    <input type="hidden" name="id" value="<?= $productData['id'] ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           value="<?= htmlspecialchars($productData['name'] ?? '') ?>" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category *</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select a category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" 
                                                    <?= ($productData['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="price" class="form-label">Price ($) *</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="price" 
                                                   name="price" 
                                                   step="0.01" 
                                                   min="0" 
                                                   value="<?= $productData['price'] ?? '' ?>" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="stock" class="form-label">Stock *</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="stock" 
                                                   name="stock" 
                                                   min="0" 
                                                   value="<?= $productData['stock'] ?? '' ?>" 
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="image_url" class="form-label">Image URL</label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="image_url" 
                                           name="image_url" 
                                           value="<?= htmlspecialchars($productData['image_url'] ?? '') ?>"
                                           placeholder="https://example.com/image.jpg">
                                    <div class="form-text">Optional: Enter a valid image URL</div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="/modular-store/public/admin/products" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?> Product
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>