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

// Sistema de búsqueda
$search = sanitize($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$whereClause = '';
$params = [];
$whereParts = [];

if ($search) {
    $whereParts[] = "(o.id = ? OR o.shipping_name LIKE ? OR o.shipping_email LIKE ? OR o.stripe_id LIKE ?)";
    $searchId = is_numeric($search) ? (int)$search : 0;
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchId, $searchTerm, $searchTerm, $searchTerm]);
}

if ($status_filter && in_array($status_filter, ['pending', 'paid', 'shipped', 'cancelled'])) {
    $whereParts[] = "o.status = ?";
    $params[] = $status_filter;
}

if ($whereParts) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereParts);
}

// Obtener orders con búsqueda
$sql = "SELECT o.id, o.user_id, o.stripe_id, o.total, o.status, o.created_at, 
               o.shipping_name, o.shipping_email, o.shipping_address, o.phone, 
               o.card_last4, o.card_brand, 
               COUNT(oi.id) as item_count
        FROM orders o 
        LEFT JOIN order_items oi ON oi.order_id = o.id 
        $whereClause
        GROUP BY o.id 
        ORDER BY o.created_at DESC";

$stmt = Database::conn()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
        <h1 class="mb-4">Orders Management</h1>
        
        <!-- Formulario de búsqueda y filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Search Orders</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by ID, customer name, email, or Stripe ID..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Filter</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= $status_filter === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="?" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mostrar resultados de búsqueda -->
        <?php if ($search || $status_filter): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <?php if ($search && $status_filter): ?>
                    Search results for: <strong>"<?= htmlspecialchars($search) ?>"</strong> 
                    with status: <strong><?= ucfirst($status_filter) ?></strong>
                <?php elseif ($search): ?>
                    Search results for: <strong>"<?= htmlspecialchars($search) ?>"</strong>
                <?php else: ?>
                    Filtered by status: <strong><?= ucfirst($status_filter) ?></strong>
                <?php endif; ?>
                (<?= count($orders) ?> orders found)
            </div>
        <?php endif; ?>
        
        <?php if (!$orders): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= ($search || $status_filter) ? 'No orders found matching your search criteria.' : 'No orders available.' ?>
            </div>
        <?php else: ?>
            <!-- Estadísticas rápidas -->
            <div class="row mb-4">
                <?php
                $stats = [
                    'total' => count($orders),
                    'pending' => count(array_filter($orders, fn($o) => $o['status'] === 'pending')),
                    'paid' => count(array_filter($orders, fn($o) => $o['status'] === 'paid')),
                    'shipped' => count(array_filter($orders, fn($o) => $o['status'] === 'shipped')),
                    'cancelled' => count(array_filter($orders, fn($o) => $o['status'] === 'cancelled')),
                    'total_revenue' => array_sum(array_map(fn($o) => (float)$o['total'], array_filter($orders, fn($o) => $o['status'] === 'paid')))
                ];
                ?>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <h6 class="text-primary mb-0"><?= $stats['total'] ?></h6>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <h6 class="text-warning mb-0"><?= $stats['pending'] ?></h6>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <h6 class="text-success mb-0"><?= $stats['paid'] ?></h6>
                            <small class="text-muted">Paid</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <h6 class="text-info mb-0"><?= $stats['shipped'] ?></h6>
                            <small class="text-muted">Shipped</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <h6 class="text-danger mb-0"><?= $stats['cancelled'] ?></h6>
                            <small class="text-muted">Cancelled</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center">
                        <div class="card-body py-2">
                            <h6 class="text-success mb-0">$<?= number_format($stats['total_revenue'], 2) ?></h6>
                            <small class="text-muted">Revenue</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Total</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong>#<?= $order['id'] ?></strong>
                                    <?php if ($order['stripe_id']): ?>
                                        <br><small class="text-muted"><?= substr($order['stripe_id'], 0, 15) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($order['shipping_name']) ?>
                                    <?php if ($order['phone']): ?>
                                        <br><small class="text-muted"><i class="fas fa-phone"></i> <?= htmlspecialchars($order['phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($order['shipping_email']) ?></small>
                                </td>
                                <td>
                                    <strong>$<?= number_format((float)$order['total'], 2) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= $order['item_count'] ?></span>
                                </td>
                                <td>
                                    <form method="post" class="d-inline" action="?action=update_status<?= $search ? '&search=' . urlencode($search) : '' ?><?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?>">
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
                                <td>
                                    <small>
                                        <?= date('M j, Y', strtotime($order['created_at'])) ?><br>
                                        <?= date('H:i', strtotime($order['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($order['card_last4']): ?>
                                        <small>
                                            <i class="fas fa-credit-card"></i>
                                            <?= ucfirst($order['card_brand']) ?> ****<?= $order['card_last4'] ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?action=view&id=<?= $order['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
    <?php elseif ($action === 'view' && isset($order)): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Order #<?= $order['id'] ?></h1>
            <div>
                <?php if ($search || $status_filter): ?>
                    <a href="?<?= http_build_query(['search' => $search, 'status' => $status_filter]) ?>" 
                       class="btn btn-secondary me-2">← Back to Search Results</a>
                <?php else: ?>
                    <a href="?" class="btn btn-secondary me-2">← Back to Orders</a>
                <?php endif; ?>
                <span class="badge bg-<?= $order['status'] === 'paid' ? 'success' : ($order['status'] === 'shipped' ? 'info' : ($order['status'] === 'cancelled' ? 'danger' : 'warning')) ?> fs-6">
                    <?= ucfirst($order['status']) ?>
                </span>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-user me-2"></i>Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($order['shipping_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($order['shipping_email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>
                        <p><strong>Address:</strong><br><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-receipt me-2"></i>Order Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Order ID:</strong> #<?= $order['id'] ?></p>
                        <p><strong>Stripe ID:</strong> 
                            <code><?= htmlspecialchars($order['stripe_id']) ?></code>
                        </p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?= $order['status'] === 'paid' ? 'success' : ($order['status'] === 'shipped' ? 'info' : ($order['status'] === 'cancelled' ? 'danger' : 'warning')) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </p>
                        <p><strong>Total:</strong> <span class="h5 text-success">$<?= number_format((float)$order['total'], 2) ?></span></p>
                        <p><strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                        <?php if ($order['card_last4']): ?>
                            <p><strong>Payment:</strong> 
                                <i class="fas fa-credit-card"></i>
                                <?= ucfirst($order['card_brand']) ?> ending in <?= $order['card_last4'] ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-shopping-cart me-2"></i>Order Items</h5>
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
                                    <td><span class="badge bg-secondary"><?= $item['quantity'] ?></span></td>
                                    <td>$<?= number_format((float)$item['price_each'], 2) ?></td>
                                    <td><strong>$<?= number_format((float)$item['price_each'] * $item['quantity'], 2) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="3">Total</th>
                                <th class="h5">$<?= number_format((float)$order['total'], 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Order not found
        </div>
        <a href="?" class="btn btn-primary">← Back to Orders</a>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>