<?php
declare(strict_types=1);

function requireAdmin(): void
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /modular-store/modules/admin/controllers/login.php');
        exit;
    }
}

function isAdmin(): bool
{
    return isset($_SESSION['admin_id']);
}

function adminLogout(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_email']);
    header('Location: /modular-store/modules/admin/controllers/login.php');
    exit;
}