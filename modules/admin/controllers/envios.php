<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$message = '';

// Procesar cambios de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_shipping_status'])) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? '';
    
    $allowedStatuses = ['shipped', 'cancelled', 'returned'];
    if ($orderId && in_array($newStatus, $allowedStatuses)) {
        $stmt = Database::conn()->prepare("UPDATE orders SET status = ? WHERE id = ? AND status = 'paid'");
        $stmt->execute([$newStatus, $orderId]);
        $message = ucfirst($newStatus) . ' status updated successfully';
    }
}

// Obtener pedidos pagados pendientes de envío
$stmt = Database::conn()->prepare(
    "SELECT o.id, o.shipping_name, o.shipping_email, o.shipping_address, o.total, o.created_at,
            COUNT(oi.id) as item_count
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     WHERE o.status = 'paid'
     GROUP BY o.id
     ORDER BY o.created_at ASC"
);
$stmt->execute();
$pedidosEnvio = $stmt->fetchAll();

$totalPendientes = count($pedidosEnvio);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Gestión de Envíos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/admin/controllers/dashboard.php">← Admin Panel</a>
        <span class="navbar-brand">Gestión de Envíos</span>
    </div>
</nav>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Pedidos Pendientes de Envío</h1>
        <span class="badge bg-warning fs-6"><?= $totalPendientes ?> pendientes</span>
    </div>
    
    <?php if ($pedidosEnvio): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Total</th>
                        <th>Items</th>
                        <th>Fecha Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidosEnvio as $pedido): ?>
                        <tr>
                            <td><strong>#<?= $pedido['id'] ?></strong></td>
                            <td><?= htmlspecialchars($pedido['shipping_name']) ?></td>
                            <td><?= htmlspecialchars($pedido['shipping_email']) ?></td>
                            <td>
                                <small><?= htmlspecialchars($pedido['shipping_address']) ?></small>
                            </td>
                            <td><strong>$<?= number_format((float)$pedido['total'], 2) ?></strong></td>
                            <td><?= $pedido['item_count'] ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($pedido['created_at'])) ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="update_shipping_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $pedido['id'] ?>">
                                    <div class="btn-group" role="group">
                                        <button type="submit" name="new_status" value="shipped" 
                                                class="btn btn-success btn-sm" title="Marcar como Enviado"
                                                onclick="return confirm('¿Marcar como enviado?')">
                                            <i class="fas fa-truck me-1"></i>Enviado
                                        </button>
                                        <button type="submit" name="new_status" value="cancelled" 
                                                class="btn btn-danger btn-sm" title="Cancelar Pedido"
                                                onclick="return confirm('¿Cancelar este pedido?')">
                                            <i class="fas fa-times me-1"></i>Cancelar
                                        </button>
                                        <button type="submit" name="new_status" value="returned" 
                                                class="btn btn-warning btn-sm" title="Marcar como Devuelto"
                                                onclick="return confirm('¿Marcar como devuelto?')">
                                            <i class="fas fa-undo me-1"></i>Devuelto
                                        </button>
                                    </div>
                                </form>
                                <a href="/modular-store/modules/admin/controllers/orders.php?action=view&id=<?= $pedido['id'] ?>" 
                                   class="btn btn-outline-primary btn-sm ms-2" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
            <h3>¡Excelente trabajo!</h3>
            <p class="text-muted">No hay pedidos pendientes de envío</p>
            <a href="/modular-store/modules/admin/controllers/orders.php" class="btn btn-primary">
                Ver todos los pedidos
            </a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>