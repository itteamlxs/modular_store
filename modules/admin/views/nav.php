<?php
// modules/admin/views/nav.php
?>
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/admin/controllers/dashboard.php">
            <i class="fas fa-home me-2"></i>Admin Panel
        </a>
        <div class="navbar-nav flex-row">
            <a class="nav-link me-3" href="/modular-store/modules/admin/controllers/products.php">
                <i class="fas fa-box me-1"></i>Products
            </a>
            <a class="nav-link me-3" href="/modular-store/modules/admin/controllers/orders.php">
                <i class="fas fa-shopping-cart me-1"></i>Orders
            </a>
            <a class="nav-link me-3" href="/modular-store/modules/admin/controllers/envios.php">
                <i class="fas fa-truck me-1"></i>Envíos
            </a>
            <a class="nav-link me-3" href="/modular-store/modules/admin/controllers/users.php">
                <i class="fas fa-users me-1"></i>Admins
            </a>
            <a class="nav-link me-3" href="/modular-store/modules/admin/controllers/reports.php">
                <i class="fas fa-chart-bar me-1"></i>Reports
            </a>
            <a class="nav-link me-3" href="/modular-store/modules/catalog/views/list.php">
                <i class="fas fa-store me-1"></i>Store
            </a>
        </div>
        <div>
            <span class="text-light me-3">
                <i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['admin_email']) ?>
            </span>
            <a href="/modular-store/modules/admin/controllers/logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>
</nav>