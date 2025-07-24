<?php
$total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $items));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/catalog/views/list.php">← Catalog</a>
        <span class="navbar-brand">Cart</span>
    </div>
</nav>

<div class="container">
    <h1>Your Cart</h1>

    <?php if (!$items): ?>
        <div class="alert alert-info">Cart is empty.</div>
        <a class="btn btn-primary" href="/modular-store/modules/catalog/views/list.php">Continue shopping</a>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 300px;">Product</th>
                        <th style="width: 140px;">Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $i): ?>
                    <tr>
                        <td class="d-flex align-items-center">
                            <?php if ($i['image_url']): ?>
                                <img src="<?= htmlspecialchars($i['image_url']) ?>" alt="" class="img-thumbnail me-2" style="width: 60px; height: 40px; object-fit: cover;">
                            <?php endif; ?>
                            <?= htmlspecialchars($i['name']) ?>
                        </td>
                        <td>
                            <form action="/modular-store/modules/cart/controllers/update.php" method="post" class="d-inline">
                                <input type="hidden" name="product_id" value="<?= $i['id'] ?>">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary" type="submit" name="delta" value="-1">-</button>
                                    <input type="text" class="form-control text-center" value="<?= $i['qty'] ?>" readonly style="max-width:50px;">
                                    <button class="btn btn-outline-secondary" type="submit" name="delta" value="1">+</button>
                                </div>
                            </form>
                        </td>
                        <td>$<?= number_format($i['price'], 2) ?></td>
                        <td>$<?= number_format($i['price'] * $i['qty'], 2) ?></td>
                        <td>
                            <form action="/modular-store/modules/cart/controllers/remove.php" method="post" class="d-inline">
                                <input type="hidden" name="product_id" value="<?= $i['id'] ?>">
                                <button class="btn btn-sm btn-danger">×</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th>$<?= number_format($total, 2) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between">
            <a class="btn btn-outline-danger" href="/modular-store/modules/cart/controllers/empty.php">Empty Cart</a>
            <a class="btn btn-success" href="/modular-store/modules/checkout/controllers/index.php">Checkout</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>