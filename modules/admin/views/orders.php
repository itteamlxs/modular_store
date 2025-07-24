<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Admin - Órdenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/admin/controllers/dashboard.php">Admin Panel</a>
        <a href="/modular-store/modules/admin/controllers/logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
    </div>
</nav>

<div class="container mt-4">
    <h2>Órdenes</h2>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= $o['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($o['shipping_name']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($o['shipping_email']) ?></small>
                    </td>
                    <td>$<?= number_format($o['total'], 2) ?></td>
                    <td>
                        <span class="badge bg-<?= $o['status'] === 'paid' ? 'success' : ($o['status'] === 'pending' ? 'warning' : 'info') ?>">
                            <?= ucfirst($o['status']) ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td>
                        <form class="d-inline" method="post" action="/modular-store/modules/admin/controllers/order-update.php">
                            <input type="hidden" name="id" value="<?= $o['id'] ?>">
                            <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                <option value="pending" <?= $o['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="paid" <?= $o['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="shipped" <?= $o['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="cancelled" <?= $o['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>