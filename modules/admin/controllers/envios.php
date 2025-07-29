<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$message = '';
$search = sanitize($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_shipping_status'])) {
    $shipmentId = (int)($_POST['shipment_id'] ?? 0);
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? '';
    $notes = sanitize($_POST['notes'] ?? '');
    
    $allowedStatuses = ['shipped', 'cancelled', 'returned'];
    if ($orderId && in_array($newStatus, $allowedStatuses)) {
        
        $trackingNumber = '';
        if ($newStatus === 'shipped') {
            $trackingNumber = 'TRK' . date('Ymd') . str_pad((string)$orderId, 4, '0', STR_PAD_LEFT) . rand(100, 999);
        }
        
        if (!$shipmentId) {
            $stmt = Database::conn()->prepare(
                "INSERT INTO shipments (order_id, status, tracking_number, shipped_at, notes) VALUES (?, ?, ?, ?, ?)"
            );
            $shippedAt = $newStatus === 'shipped' ? date('Y-m-d H:i:s') : null;
            $stmt->execute([$orderId, $newStatus, $trackingNumber, $shippedAt, $notes]);
        } else {
            $stmt = Database::conn()->prepare(
                "UPDATE shipments SET status = ?, tracking_number = ?, shipped_at = ?, notes = ?, updated_at = NOW() WHERE id = ?"
            );
            $shippedAt = $newStatus === 'shipped' ? date('Y-m-d H:i:s') : null;
            $stmt->execute([$newStatus, $trackingNumber, $shippedAt, $notes, $shipmentId]);
        }
        $message = 'Shipment status updated successfully';
    }
}

// Construcción de consulta con filtros
$sql = "SELECT * FROM v_shipments WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (shipping_name LIKE ? OR shipping_email LIKE ? OR order_id LIKE ? OR tracking_number LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if ($status) {
    $sql .= " AND shipment_status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY order_date DESC";

$stmt = Database::conn()->prepare($sql);
$stmt->execute($params);
$envios = $stmt->fetchAll();

$totalPendientes = count(array_filter($envios, fn($e) => $e['shipment_status'] === 'pending'));
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

<?php include __DIR__ . '/../views/nav.php'; ?>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Envíos</h1>
        <span class="badge bg-warning fs-6"><?= $totalPendientes ?> pendientes</span>
    </div>
    
    <!-- Filtros de búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Buscar por cliente, email, order ID o tracking..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Todos los estados</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Enviado</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                        <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Devuelto</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="?" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($envios): ?>
        <div class="mb-3">
            <small class="text-muted"><?= count($envios) ?> resultados encontrados</small>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Cliente</th>
                        <th>Dirección</th>
                        <th>Total</th>
                        <th>Items</th>
                        <th>Estado Envío</th>
                        <th>Tracking</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($envios as $envio): ?>
                        <tr class="<?= $envio['shipment_status'] === 'pending' ? 'table-warning' : '' ?>">
                            <td><strong>#<?= $envio['order_id'] ?></strong></td>
                            <td>
                                <?= htmlspecialchars($envio['shipping_name']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($envio['shipping_email']) ?></small>
                            </td>
                            <td><small><?= htmlspecialchars($envio['shipping_address']) ?></small></td>
                            <td><strong>$<?= number_format((float)$envio['total'], 2) ?></strong></td>
                            <td><?= $envio['item_count'] ?></td>
                            <td>
                                <span class="badge bg-<?= $envio['shipment_status'] === 'pending' ? 'warning' : 
                                    ($envio['shipment_status'] === 'shipped' ? 'success' : 
                                    ($envio['shipment_status'] === 'returned' ? 'info' : 'danger')) ?>">
                                    <?= ucfirst($envio['shipment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= $envio['tracking_number'] ? htmlspecialchars($envio['tracking_number']) : '-' ?>
                            </td>
                            <td>
                                <?php if ($envio['shipment_status'] === 'pending'): ?>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="update_shipping_status" value="1">
                                        <input type="hidden" name="shipment_id" value="<?= $envio['id'] ?>">
                                        <input type="hidden" name="order_id" value="<?= $envio['order_id'] ?>">
                                        
                                        <div class="mb-2">
                                            <input type="text" name="notes" class="form-control form-control-sm" 
                                                   placeholder="Notas del envío (opcional)" value="<?= htmlspecialchars($envio['notes'] ?? '') ?>">
                                        </div>
                                        
                                        <div class="btn-group" role="group">
                                            <button type="submit" name="new_status" value="shipped" 
                                                    class="btn btn-success btn-sm" title="Marcar como Enviado">
                                                <i class="fas fa-truck"></i> Enviar
                                            </button>
                                            <button type="submit" name="new_status" value="cancelled" 
                                                    class="btn btn-danger btn-sm" title="Cancelar">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                            <button type="submit" name="new_status" value="returned" 
                                                    class="btn btn-warning btn-sm" title="Devuelto">
                                                <i class="fas fa-undo"></i> Devolver
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="small">
                                        <?php if ($envio['shipped_at']): ?>
                                            <strong><?= date('M j, Y H:i', strtotime($envio['shipped_at'])) ?></strong><br>
                                        <?php endif; ?>
                                        <?php if ($envio['notes']): ?>
                                            <em class="text-muted"><?= htmlspecialchars($envio['notes']) ?></em>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    <?php else: ?>
        <div class="text-center py-5">
            <?php if ($search || $status): ?>
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h3>No se encontraron resultados</h3>
                <p class="text-muted">Intenta con otros términos de búsqueda</p>
                <a href="?" class="btn btn-primary">Ver todos los envíos</a>
            <?php else: ?>
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h3>No hay pedidos para enviar</h3>
                <p class="text-muted">Los envíos aparecerán aquí cuando haya pedidos pagados</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>
</body>
</html>