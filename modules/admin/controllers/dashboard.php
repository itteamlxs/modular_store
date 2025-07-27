<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

// Get stats
$totalProducts = Database::conn()->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];
$totalOrders = Database::conn()->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
$totalUsers = Database::conn()->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1")->fetch()['count'];
$totalRevenue = Database::conn()->query("SELECT SUM(total) as revenue FROM orders WHERE status = 'paid'")->fetch()['revenue'] ?? 0;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand">Admin Panel</span>
        <div>
            <span class="text-light me-3">Welcome, <?= htmlspecialchars($_SESSION['admin_email']) ?></span>
            <a href="/modular-store/modules/admin/controllers/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="mb-4">Dashboard</h1>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= $totalProducts ?></h3>
                    <p class="card-text">Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?= $totalOrders ?></h3>
                    <p class="card-text">Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info"><?= $totalUsers ?></h3>
                    <p class="card-text">Admins</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">$<?= number_format((float)$totalRevenue, 2) ?></h3>
                    <p class="card-text">Revenue</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="/modular-store/modules/admin/controllers/products.php" class="btn btn-primary me-2">Manage Products</a>
                    <a href="/modular-store/modules/admin/controllers/orders.php" class="btn btn-success me-2">Manage Orders</a>
                    <a href="/modular-store/modules/admin/controllers/envios.php" class="btn btn-warning me-2">Gestión de Envíos</a>
                    <a href="/modular-store/modules/admin/controllers/users.php" class="btn btn-info me-2">Manage Admins</a>
                    <a href="/modular-store/modules/catalog/views/list.php" class="btn btn-outline-secondary">View Store</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Timezone Converter -->
    <?php include __DIR__ . '/../views/timezone_converter.php'; ?>
    
    <!-- Currency Converter -->
    <?php include __DIR__ . '/../views/currency_converter.php'; ?>
    
    <!-- All Orders -->
    <div class="row">
        <div class="col-md-12">
            <?php include __DIR__ . '/../views/orders_widget.php'; ?>
        </div>
    </div>
</div>
</body>
</html>