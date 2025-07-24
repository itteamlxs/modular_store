<?php
declare(strict_types=1);

class AdminUserController
{
    public function createForm(): void
    {
        $this->requireAuth();
        require __DIR__ . '/../views/user_form.php';
    }

    public function store(): void
    {
        $this->requireAuth();

        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (!$email || !$password || !$confirmPassword) {
            header('Location: /modular-store/public/admin/users/new?error=missing_fields');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /modular-store/public/admin/users/new?error=invalid_email');
            exit;
        }

        if ($password !== $confirmPassword) {
            header('Location: /modular-store/public/admin/users/new?error=password_mismatch');
            exit;
        }

        if (strlen($password) < 6) {
            header('Location: /modular-store/public/admin/users/new?error=password_short');
            exit;
        }

        // Check if email already exists
        $existing = Database::conn()->prepare("SELECT id FROM users WHERE email = ?");
        $existing->execute([$email]);
        if ($existing->fetch()) {
            header('Location: /modular-store/public/admin/users/new?error=email_exists');
            exit;
        }

        // Create admin user
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = Database::conn()->prepare(
            "INSERT INTO users (email, password_hash, is_admin) VALUES (?, ?, 1)"
        );
        $stmt->execute([$email, $passwordHash]);

        header('Location: /modular-store/public/admin?success=admin_created');
        exit;
    }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /modular-store/public/admin/login');
            exit;
        }
    }
}