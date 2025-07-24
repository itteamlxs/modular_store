<?php if (!isset($_SESSION['admin_id'])) { die('Access denied'); } ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Orders Management</title>
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
                        <a class="nav-link active" href="/modular-store/public/admin/orders">
                            <i class="fas fa-shopping-bag"></i> Orders
                        </a>
                        <a class="nav-link" href="/modular-store/public/admin/products">
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
                <h1 class="h3">Orders Management</h1>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <p class="text-muted">No orders found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="/modular-store/public/admin/orders/detail?id=<?= $order['id'] ?>" class="text-decoration-none">
                                                    #<?= $order['id'] ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($order['shipping_name']) ?></td>
                                            <td><?= htmlspecialchars($order['shipping_email']) ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $order['item_count'] ?> items</span>
                                            </td>
                                            <td>$<?= number_format($order['total'], 2) ?></td>
                                            <td>
                                                <form action="/modular-store/public/admin/orders/status" method="post" class="d-inline">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="paid" <?= $order['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?= date('M j, Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <a href="/modular-store/public/admin/orders/detail?id=<?= $order['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
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