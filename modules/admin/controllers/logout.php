<?php
declare(strict_types=1);

class AdminLogoutController
{
    public function logout(): void
    {
        unset($_SESSION['admin_id']);
        header('Location: /admin/login');
        exit;
    }
}