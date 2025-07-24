<?php if (!isset($_SESSION['admin_id'])) { die('Access denied'); } ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Categories Management</title>
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
                        <a class="nav-link" href="/modular-store/public/admin/products">
                            <i class="fas fa-box"></i> Products
                        </a>
                        <a class="nav-link active" href="/modular-store/public/admin/categories">
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
                <h1 class="h3">Categories Management</h1>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php if ($_GET['error'] === 'name_required'): ?>
                        Category name is required.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Add Category Form -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Add New Category</h5>
                        </div>
                        <div class="card-body">
                            <form action="/modular-store/public/admin/categories" method="post">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus"></i> Add Category
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Categories List -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>Categories List</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($categories)): ?>
                                <p class="text-muted">No categories found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Products</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                                    <td><?= htmlspecialchars($category['description'] ?? 'No description') ?></td>
                                                    <td>
                                                        <span class="badge bg-info"><?= $category['product_count'] ?> products</span>
                                                    </td>
                                                    <td>
                                                        <?php if ($category['product_count'] == 0): ?>
                                                            <form action="/modular-store/public/admin/categories/delete" 
                                                                  method="post" 
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('Are you sure?')">
                                                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-outline-secondary" 
                                                                    disabled 
                                                                    title="Cannot delete category with products">
                                                                <i class="fas fa-lock"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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