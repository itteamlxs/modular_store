<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';

// Parámetros de búsqueda y paginación
$search = sanitize($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Construir consulta base
$whereClause = '';
$params = [];

if ($search) {
    $whereClause = 'WHERE name LIKE ? OR category LIKE ?';
    $params = ["%$search%", "%$search%"];
}

// Contar total de productos
$countSql = "SELECT COUNT(*) as total FROM v_products $whereClause";
$countStmt = Database::conn()->prepare($countSql);
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetch()['total'];
$totalPages = ceil($totalProducts / $perPage);

// Obtener productos paginados
$sql = "SELECT * FROM v_products $whereClause ORDER BY name ASC LIMIT $perPage OFFSET $offset";
$stmt = Database::conn()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!-- Formulario de búsqueda -->
<form method="GET" class="row mb-4">
    <div class="col-md-8">
        <input type="text" name="search" class="form-control" 
               placeholder="Search products..." 
               value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Search</button>
    </div>
    <div class="col-md-2">
        <a href="/modular-store/modules/catalog/views/list.php" class="btn btn-outline-secondary w-100">Clear</a>
    </div>
</form>

<!-- Información de búsqueda -->
<?php if ($search): ?>
    <div class="alert alert-info">
        Search results for: <strong><?= htmlspecialchars($search) ?></strong> 
        (<?= $totalProducts ?> products found)
    </div>
<?php endif; ?>

<!-- Grid de productos -->
<?php if (!$products): ?>
    <div class="alert alert-warning">
        <?= $search ? 'No products found for your search.' : 'No products available.' ?>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
        <?php foreach ($products as $p): ?>
            <div class="col">
                <div class="card h-100">
                    <?php if ($p['image_url']): ?>
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($p['name']) ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($p['category']) ?></p>
                        <p class="fw-bold">$<?= number_format((float)$p['price'], 2) ?></p>
                        <form action="/modular-store/modules/cart/controllers/add.php" method="post">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button class="btn btn-primary btn-sm">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Products pagination">
            <ul class="pagination justify-content-center">
                <!-- Botón anterior -->
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <!-- Números de página -->
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Botón siguiente -->
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Información de paginación -->
        <div class="text-center text-muted">
            Showing <?= $offset + 1 ?>-<?= min($offset + $perPage, $totalProducts) ?> of <?= $totalProducts ?> products
        </div>
    <?php endif; ?>
<?php endif; ?>