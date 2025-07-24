<?php if (!isset($_SESSION['admin_id'])) { die('Access denied'); } ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h1>Dashboard</h1>
    <a class="btn btn-outline-primary" href="/modular-store/public/admin/users/new">Add admin</a>
    <a class="btn btn-outline-danger" href="/modular-store/public/admin/logout">Logout</a>

    <table class="table mt-3">
        <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['shipping_name']) ?></td>
                <td>$<?= $o['total'] ?></td>
                <td><?= $o['status'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>