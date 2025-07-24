<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Admin Panel</span>
        <div>
            <a href="/modular-store/modules/admin/controllers/products.php" class="btn btn-outline-light btn-sm me-2">Productos</a>
            <a href="/modular-store/modules/admin/controllers/orders.php" class="btn btn-outline-light btn-sm me-2">Órdenes</a>
            <a href="/modular-store/modules/admin/controllers/users.php" class="btn btn-outline-light btn-sm me-2">Usuarios</a>
            <a href="/modular-store/modules/admin/controllers/logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1>Dashboard</h1>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><?= $stats['products'] ?></h5>
                    <p class="card-text">Productos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><?= $stats['orders'] ?></h5>
                    <p class="card-text">Órdenes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><?= $stats['users'] ?></h5>
                    <p class="card-text">Usuarios</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">$<?= number_format($stats['revenue'], 2) ?></h5>
                    <p class="card-text">Ingresos</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>