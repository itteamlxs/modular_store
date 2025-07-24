<?php
declare(strict_types=1);

class AdminLogoutController
{
    public function logout(): void
    {
        session_destroy();
        header('Location: /modular-store/public/admin/login');
        exit;
    }
}