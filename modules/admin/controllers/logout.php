<?php
declare(strict_types=1);

session_start();
unset($_SESSION['admin_id']);
header('Location: /modular-store/admin/login');