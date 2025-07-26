<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_status') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    $allowedStatuses = ['pending', 'paid', 'shipped', 'cancelled'];
    if ($id && in_array($status, $allowedStatuses)) {
        $stmt = Database::conn()->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $message = 'Order status updated successfully';
    }
}

$orders = Database::view('v_admin_orders');

if ($action === 'view' && isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    $order = Database::view('v_admin_orders', ['id' => $orderId])[0] ?? null;
    if ($order) {
        // Get order items
        $stmt = Database::conn()->prepare(
            "SELECT oi.*, p.name as product_name 
             FROM order_items oi 
             JOIN products p ON p.id = oi.product_id 
             WHERE oi.order_id = ?"
        );
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/admin/controllers/dashboard.php">← Admin Panel</a>
        <span class="navbar-brand">Manage Orders</span>
    </div>
</nav>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if ($action === 'list'): ?>
        <h1 class="mb-4">Orders</h1>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['shipping_name']) ?></td>
                            <td><?= htmlspecialchars($order['shipping_email']) ?></td>
                            <td>$<?= number_format((float)$order['total'], 2) ?></td>
                            <td><?= $order['item_count'] ?></td>
                            <td>
                                <form method="post" class="d-inline" action="?action=update_status">
                                    <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" 
                                            onchange="this.form.submit()" style="width: auto;">
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="paid" <?= $order['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            <td>
                                <a href="?action=view&id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    <?php elseif ($action === 'view' && isset($order)): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Order #<?= $order['id'] ?></h1>
            <a href="?" class="btn btn-secondary">← Back to Orders</a>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($order['shipping_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($order['shipping_email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Order Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Order ID:</strong> #<?= $order['id'] ?></p>
                        <p><strong>Stripe ID:</strong> <?= htmlspecialchars($order['stripe_id']) ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?= $order['status'] === 'paid' ? 'success' : ($order['status'] === 'shipped' ? 'info' : 'warning') ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </p>
                        <p><strong>Total:</strong> $<?= number_format((float)$order['total'], 2) ?></p>
                        <p><strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                        <?php if ($order['card_last4']): ?>
                            <p><strong>Payment:</strong> <?= ucfirst($order['card_brand']) ?> **** <?= $order['card_last4'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
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
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>$<?= number_format((float)$item['price_each'], 2) ?></td>
                                    <td>$<?= number_format((float)$item['price_each'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total</th>
                                <th>$<?= number_format((float)$order['total'], 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <div class="alert alert-warning">Order not found</div>
        <a href="?" class="btn btn-primary">← Back to Orders</a>
    <?php endif; ?>
</div>
</body>
</html>