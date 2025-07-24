<?php if (!isset($_SESSION['admin_id'])) { die('Access denied'); } ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Add Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap/5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-4" style="max-width: 400px;">
    <h2>Add New Admin</h2>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-warning">Password must be ≥ 9 chars, 1 upper, 1 lower, 1 digit, 1 special.</div>
    <?php endif; ?>
    <form action="/modular-store/admin/users" method="post">
        <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
        <button class="btn btn-primary w-100">Create</button>
    </form>
</div></body></html>