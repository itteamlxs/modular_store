<?php
declare(strict_types=1);

class AdminUserController
{
    public function createForm(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
        require __DIR__ . '/../views/user_form.php';
    }

    public function store(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(403);
            exit('Forbidden');
        }

        $email = $_POST['email'] ?? '';
        $pwd   = $_POST['password'] ?? '';

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{9,}$/', $pwd)) {
            header('Location: /admin/users/new?error=weak');
            exit;
        }

        $hash = password_hash($pwd, PASSWORD_DEFAULT);
        $stmt = Database::conn()->prepare(
            "INSERT INTO users (email, password_hash, is_admin) VALUES (?, ?, ?)"
        );
        $stmt->execute([$email, $hash, 1]);
        header('Location: /admin');
    }
}