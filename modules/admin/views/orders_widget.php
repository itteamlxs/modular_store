<?php
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total orders count
$totalOrders = Database::conn()->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
$totalPages = ceil($totalOrders / $limit);

// Get paginated orders
$stmt = Database::conn()->prepare(
    "SELECT o.id, o.shipping_name, o.shipping_email, o.total, o.status, o.created_at,
            COUNT(oi.id) as item_count
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     GROUP BY o.id
     ORDER BY o.created_at DESC
     LIMIT ? OFFSET ?"
);
$stmt->execute([$limit, $offset]);
$orders = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Orders (<?= $totalOrders ?>)</h5>
        <a href="/modular-store/modules/admin/controllers/orders.php" class="btn btn-sm btn-outline-primary">
            Manage Orders
        </a>
    </div>
    <div class="card-body">
        <?php if ($orders): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Total</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['shipping_name']) ?></td>
                                <td>
                                    <small><?= htmlspecialchars($order['shipping_email']) ?></small>
                                </td>
                                <td>$<?= number_format((float)$order['total'], 2) ?></td>
                                <td><?= $order['item_count'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $order['status'] === 'paid' ? 'success' : ($order['status'] === 'shipped' ? 'info' : 'warning') ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= date('M j, Y', strtotime($order['created_at'])) ?></small>
                                </td>
                                <td>
                                    <a href="/modular-store/modules/admin/controllers/orders.php?action=view&id=<?= $order['id'] ?>" 
                                       class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination pagination-sm justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <p class="text-muted text-center">No orders found</p>
        <?php endif; ?>
    </div>
</div>