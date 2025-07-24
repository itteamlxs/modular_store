<?php
declare(strict_types=1);

class AdminDashboardController
{
    public function index(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
        $orders = Database::view('v_orders');
        require __DIR__ . '/../views/dashboard.php';
    }
}