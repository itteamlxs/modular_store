<?php if (!isset($_SESSION['admin_id'])) { die('Access denied'); } ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Products Management</title>
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
                <h1 class="h3">Products Management</h1>
                <a href="/modular-store/public/admin/products/new" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($products)): ?>
                        <p class="text-muted">No products found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <?php if ($product['image_url']): ?>
                                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                                         alt="" class="img-thumbnail" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                                            <td>$<?= number_format($product['price'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $product['stock'] <= 5 ? 'warning' : 'success' ?>">
                                                    <?= $product['stock'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($product['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/modular-store/public/admin/products/edit?id=<?= $product['id'] ?>" 
                                                       class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="/modular-store/public/admin/products/delete" method="post" class="d-inline"
                                                          onsubmit="return confirm('Are you sure?')">
                                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>