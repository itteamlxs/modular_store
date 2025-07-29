<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../admin/helpers/auth.php';

// Búsqueda
$search = sanitize($_GET['search'] ?? '');
$whereClause = '';
$params = [];

if ($search) {
    $whereClause = 'WHERE name LIKE ? OR category LIKE ?';
    $params = ["%$search%", "%$search%"];
}

$sql = "SELECT * FROM v_products $whereClause ORDER BY name ASC";
$stmt = Database::conn()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/catalog/views/list.php">Modular Store</a>
        <div>
            <a class="btn btn-outline-light btn-sm me-2" href="/modular-store/modules/cart/controllers/view.php">Cart</a>
            <?php if (isAdmin()): ?>
                <a class="btn btn-outline-success btn-sm" href="/modular-store/modules/admin/controllers/dashboard.php">Admin Panel</a>
            <?php else: ?>
                <a class="btn btn-outline-secondary btn-sm" href="/modular-store/modules/admin/controllers/login.php">Admin</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="mb-4">Product Catalog</h1>

    <!-- Formulario de búsqueda -->
    <form method="GET" class="row mb-4">
        <div class="col-md-8">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search products..." 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
        <div class="col-md-2">
            <a href="/modular-store/modules/catalog/views/list.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
    </form>

    <!-- Mostrar término de búsqueda -->
    <?php if ($search): ?>
        <div class="alert alert-info">
            Search results for: <strong><?= htmlspecialchars($search) ?></strong> 
            (<?= count($products) ?> products found)
        </div>
    <?php endif; ?>

    <!-- Grid de productos -->
    <?php if (!$products): ?>
        <div class="alert alert-warning">
            <?= $search ? 'No products found for your search.' : 'No products available.' ?>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($products as $p): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if ($p['image_url']): ?>
                            <img src="<?= htmlspecialchars($p['image_url']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($p['name']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($p['category']) ?></p>
                            <p class="fw-bold">$<?= number_format((float)$p['price'], 2) ?></p>
                            <form action="/modular-store/modules/cart/controllers/add.php" method="post">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <button class="btn btn-primary btn-sm">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>