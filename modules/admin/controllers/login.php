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

        $user = Database::conn()->prepare(
            "SELECT id, password_hash FROM users WHERE email = ? AND is_admin = 1"
        );
        $user->execute([$email]);
        $row = $user->fetch();

        if ($row && password_verify($pass, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $row['id'];
            header('Location: /modular-store/admin');
            exit;
        }
        header('Location: /modular-store/admin/login');
    }
}