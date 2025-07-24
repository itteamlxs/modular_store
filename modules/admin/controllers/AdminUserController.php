<?php
declare(strict_types=1);

class AdminUserController
{
    private function checkAuth(): void
    {
        if (!($_SESSION['is_admin'] ?? false)) {
            header('Location: /modular-store/modules/admin/controllers/login.php');
            exit;
        }
    }

    public function users(): void
    {
        $this->checkAuth();
        $users = Database::view('v_admin_users');
        require __DIR__ . '/../views/users.php';
    }

    public function userSave(): void
    {
        $this->checkAuth();
        $id = (int)($_POST['id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $is_admin = (int)($_POST['is_admin'] ?? 0);
        $password = trim($_POST['password'] ?? '');

        if ($id > 0) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = Database::conn()->prepare("UPDATE users SET email=?, is_admin=?, password_hash=? WHERE id=?");
                $stmt->execute([$email, $is_admin, $hash, $id]);
            } else {
                $stmt = Database::conn()->prepare("UPDATE users SET email=?, is_admin=? WHERE id=?");
                $stmt->execute([$email, $is_admin, $id]);
            }
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = Database::conn()->prepare("INSERT INTO users (email, password_hash, is_admin) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hash, $is_admin]);
        }
        header('Location: /modular-store/modules/admin/controllers/users.php');
        exit;
    }

    public function userDelete(): void
    {
        $this->checkAuth();
        $id = (int)($_POST['id'] ?? 0);
        if ($id != ($_SESSION['user_id'] ?? 0)) {
            Database::conn()->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        }
        header('Location: /modular-store/modules/admin/controllers/users.php');
        exit;
    }

    public function userReset(): void
    {
        $this->checkAuth();
        $id = (int)($_POST['id'] ?? 0);
        $newPass = 'Admin' . rand(1000, 9999);
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        Database::conn()->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $id]);
        $_SESSION['reset_password'] = $newPass;
        header('Location: /modular-store/modules/admin/controllers/users.php');
        exit;
    }
}