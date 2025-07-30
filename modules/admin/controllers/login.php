<?php
// modules/admin/controllers/login.php - CON AUDITORÍA
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(sanitize($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password required';
    } else {
        $user = Database::view('v_admin_users', ['email' => $email])[0] ?? null;
        
        if ($user && $user['is_admin'] && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['email'];
            
            // AUDITORÍA: Registrar LOGIN
            $stmt = Database::conn()->prepare("CALL log_user_action('LOGIN', ?, ?, ?)");
            $stmt->execute([
                $user['id'],
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            header('Location: /modular-store/modules/admin/controllers/dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials or not admin';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Admin Login</h3>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= htmlspecialchars($email ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="/modular-store/modules/catalog/views/list.php" class="text-muted">← Back to Store</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php
// modules/admin/controllers/logout.php - CON AUDITORÍA
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../helpers/auth.php';

// AUDITORÍA: Registrar LOGOUT antes de destruir sesión
if (isset($_SESSION['admin_id'])) {
    $stmt = Database::conn()->prepare("CALL log_user_action('LOGOUT', ?, ?, ?)");
    $stmt->execute([
        $_SESSION['admin_id'],
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

adminLogout();