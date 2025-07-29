<?php
// modules/catalog/views/nav.php
require_once __DIR__ . '/../../admin/helpers/auth.php';
?>
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/catalog/views/list.php">Modular Store</a>
        <div>
            <a class="btn btn-outline-light btn-sm me-2" href="/modular-store/modules/cart/controllers/view.php">
                Cart
                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="badge bg-warning text-dark"><?= array_sum($_SESSION['cart']) ?></span>
                <?php endif; ?>
            </a>
            <?php if (isAdmin()): ?>
                <a class="btn btn-outline-success btn-sm" href="/modular-store/modules/admin/controllers/dashboard.php">Admin Panel</a>
            <?php else: ?>
                <a class="btn btn-outline-secondary btn-sm" href="/modular-store/modules/admin/controllers/login.php">Admin</a>
            <?php endif; ?>
        </div>
    </div>
</nav>