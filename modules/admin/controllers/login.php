<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';

if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Debug
    error_log("Admin login attempt: $email");
    
    $user = Database::view('v_admin_users', ['email' => $email])[0] ?? null;
    
    if ($user) {
        error_log("User found: " . json_encode(['id' => $user['id'], 'email' => $user['email'], 'is_admin' => $user['is_admin']]));
        
        if (password_verify($password, $user['password_hash'])) {
            error_log("Password verified");
            
            if ($user['is_admin']) {
                error_log("User is admin, setting session");
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = true;
                $_SESSION['user_email'] = $user['email'];
                
                error_log("Session set, redirecting to dashboard");
                header('Location: /modular-store/modules/admin/controllers/dashboard.php');
                exit;
            } else {
                error_log("User is not admin");
            }
        } else {
            error_log("Password verification failed");
        }
    } else {
        error_log("User not found");
    }
    
    $error = 'Credenciales inválidas';
}

require __DIR__ . '/../views/login.php';