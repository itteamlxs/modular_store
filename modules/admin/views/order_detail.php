<?php if (!isset($_SESSION['admin_id'])) { die('Access denied'); } ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Order #<?= $orderData['id'] ?> - Details</title>
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
                <h1 class="h3">Order #<?= $orderData['id'] ?></h1>
                <a href="/modular-store/public/admin/orders" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>

            <div class="row">
                <!-- Order Info -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Order Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Order ID:</strong></div>
                                <div class="col-sm-8">#<?= $orderData['id'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Status:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-<?= $orderData['status'] === 'paid' ? 'success' : ($orderData['status'] === 'shipped' ? 'info' : 'warning') ?>">
                                        <?= ucfirst($orderData['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Total:</strong></div>
                                <div class="col-sm-8">$<?= number_format($orderData['total'], 2) ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Date:</strong></div>
                                <div class="col-sm-8"><?= date('M j, Y H:i', strtotime($orderData['created_at'])) ?></div>
                            </div>
                            <?php if ($orderData['stripe_id']): ?>
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Stripe ID:</strong></div>
                                <div class="col-sm-8"><code><?= htmlspecialchars($orderData['stripe_id']) ?></code></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Name:</strong></div>
                                <div class="col-sm-8"><?= htmlspecialchars($orderData['shipping_name']) ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Email:</strong></div>
                                <div class="col-sm-8"><?= htmlspecialchars($orderData['shipping_email']) ?></div>
                            </div>
                            <?php if ($orderData['phone']): ?>
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Phone:</strong></div>
                                <div class="col-sm-8"><?= htmlspecialchars($orderData['phone']) ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Address:</strong></div>
                                <div class="col-sm-8"><?= nl2br(htmlspecialchars($orderData['shipping_address'])) ?></div>
                            </div>
                            <?php if ($orderData['card_last4']): ?>
                            <div class="row mb-2">
                                <div class="col-sm-4"><strong>Payment:</strong></div>
                                <div class="col-sm-8">
                                    <?= ucfirst($orderData['card_brand']) ?> ****<?= $orderData['card_last4'] ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h5>Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price Each</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['image_url']): ?>
                                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                         alt="" class="img-thumbnail me-2" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                                <?= htmlspecialchars($item['name']) ?>
                                            </div>
                                        </td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td>$<?= number_format($item['price_each'], 2) ?></td>
                                        <td>$<?= number_format($item['quantity'] * $item['price_each'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th colspan="3">Total</th>
                                    <th>$<?= number_format($orderData['total'], 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>