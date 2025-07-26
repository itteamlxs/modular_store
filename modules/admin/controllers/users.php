<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../helpers/auth.php';

requireAdmin();

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Email and password are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } else {
            // Check if email exists
            $existing = Database::view('v_admin_users', ['email' => $email]);
            if ($existing) {
                $error = 'Email already exists';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = Database::conn()->prepare(
                    "INSERT INTO users (email, password_hash, is_admin) VALUES (?, ?, 1)"
                );
                $stmt->execute([$email, $hashedPassword]);
                $message = 'Admin user created successfully';
                $action = 'list';
            }
        }
    } elseif ($action === 'reset_password') {
        $id = (int)($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';
        
        if ($id === (int)$_SESSION['admin_id']) {
            $error = 'Cannot reset your own password from here';
        } elseif (empty($password) || strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = Database::conn()->prepare("UPDATE users SET password_hash = ? WHERE id = ? AND is_admin = 1");
            $stmt->execute([$hashedPassword, $id]);
            $message = 'Password reset successfully';
            $action = 'list';
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id === (int)$_SESSION['admin_id']) {
        $error = 'Cannot delete your own account';
    } else {
        Database::conn()->prepare("DELETE FROM users WHERE id = ? AND is_admin = 1")->execute([$id]);
        $message = 'Admin user deleted successfully';
    }
    $action = 'list';
}

$users = Database::view('v_admin_users');

if ($action === 'reset_password' && isset($_GET['id'])) {
    $resetUser = Database::view('v_admin_users', ['id' => (int)$_GET['id']])[0] ?? null;
    if (!$resetUser || $resetUser['id'] === (int)$_SESSION['admin_id']) {
        $action = 'list';
        $error = 'Invalid user for password reset';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Admin Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/admin/controllers/dashboard.php">← Admin Panel</a>
        <span class="navbar-brand">Manage Admin Users</span>
    </div>
</nav>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($action === 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Admin Users</h1>
            <a href="?action=create" class="btn btn-primary">Add Admin</a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($user['email']) ?>
                                <?php if ($user['id'] === (int)$_SESSION['admin_id']): ?>
                                    <span class="badge bg-primary">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <?php if ($user['id'] !== (int)$_SESSION['admin_id']): ?>
                                    <a href="?action=reset_password&id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-outline-warning">Reset Password</a>
                                    <a href="?action=delete&id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Delete this admin user?')">Delete</a>
                                <?php else: ?>
                                    <span class="text-muted">Current user</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    <?php elseif ($action === 'create'): ?>
        <h1>Add Admin User</h1>
        
        <form method="post" class="row g-3" style="max-width: 400px;">
            <div class="col-12">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            
            <div class="col-12">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" 
                       minlength="8" required>
                <div class="form-text">Minimum 8 characters</div>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Create Admin</button>
                <a href="?" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
        
    <?php elseif ($action === 'reset_password' && isset($resetUser)): ?>
        <h1>Reset Password</h1>
        <p class="text-muted">Resetting password for: <?= htmlspecialchars($resetUser['email']) ?></p>
        
        <form method="post" class="row g-3" style="max-width: 400px;">
            <input type="hidden" name="id" value="<?= $resetUser['id'] ?>">
            
            <div class="col-12">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control" name="password" 
                       minlength="8" required>
                <div class="form-text">Minimum 8 characters</div>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-warning">Reset Password</button>
                <a href="?" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>