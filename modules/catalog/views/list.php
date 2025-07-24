<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';

$products = \Database::view('v_products');
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
        <a class="btn btn-outline-light btn-sm" href="/modular-store/modules/cart/controllers/view.php">Cart</a>
    </div>
</nav>

<div class="container">
    <h1 class="mb-4">Product Catalog</h1>

    <?php if (!$products): ?>
        <div class="alert alert-warning">No products available.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($products as $p): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if ($p['image_url']): ?>
                            <img src="<?= htmlspecialchars($p['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
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