<?php
declare(strict_types=1);

class AdminLoginController
{
    public function show(): void
    {
        require __DIR__ . '/../views/login.php';
    }

    public function doLogin(): void
    {
        $email = $_POST['email'] ?? '';
        $pass  = $_POST['password'] ?? '';

        if (!$email || !$pass) {
            header('Location: /modular-store/public/admin/login?error=missing');
            exit;
        }

        try {
            $user = Database::conn()->prepare(
                "SELECT id, password_hash FROM users WHERE email = ? AND is_admin = 1"
            );
            $user->execute([$email]);
            $row = $user->fetch();

            if ($row && password_verify($pass, $row['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $row['id'];
                header('Location: /modular-store/public/admin');
                exit;
            }
            
            // Redirect back to login with error
            header('Location: /modular-store/public/admin/login?error=invalid');
            exit;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            header('Location: /modular-store/public/admin/login?error=system');
            exit;
        }
    }
}