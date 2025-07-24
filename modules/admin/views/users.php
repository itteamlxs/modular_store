<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Admin - Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/admin/controllers/dashboard.php">Admin Panel</a>
        <a href="/modular-store/modules/admin/controllers/logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h2>Usuarios</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="editUser()">Nuevo Admin</button>
    </div>

    <?php if (isset($_SESSION['reset_password'])): ?>
        <div class="alert alert-success">
            Nueva contraseña generada: <strong><?= $_SESSION['reset_password'] ?></strong>
            <?php unset($_SESSION['reset_password']); ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge bg-<?= $u['is_admin'] ? 'success' : 'secondary' ?>">
                            <?= $u['is_admin'] ? 'Sí' : 'No' ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)">Editar</button>
                        <form class="d-inline" method="post" action="/modular-store/modules/admin/controllers/user-reset.php">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button class="btn btn-sm btn-outline-warning" onclick="return confirm('¿Reset password?')">Reset</button>
                        </form>
                        <form class="d-inline" method="post" action="/modular-store/modules/admin/controllers/user-delete.php">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar?')" <?= $u['id'] == ($_SESSION['user_id'] ?? 0) ? 'disabled' : '' ?>>Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/modular-store/modules/admin/controllers/user-save.php">
                <div class="modal-header">
                    <h5 class="modal-title">Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="userId">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="userEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="userPassword">
                        <small class="text-muted">Dejar vacío para mantener actual</small>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_admin" value="1" id="userAdmin">
                            <label class="form-check-label">Es administrador</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editUser(user = {}) {
    document.getElementById('userId').value = user.id || '';
    document.getElementById('userEmail').value = user.email || '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userAdmin').checked = user.is_admin == 1;
    new bootstrap.Modal(document.getElementById('userModal')).show();
}
</script>
</body>
</html>