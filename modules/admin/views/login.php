<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 400px;">
    <h2 class="mb-3">Admin Login</h2>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php if ($_GET['error'] === 'invalid'): ?>
                Invalid email or password.
            <?php else: ?>
                Login failed. Please try again.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <form action="/modular-store/public/admin/login" method="post">
        <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
        <button class="btn btn-primary w-100">Login</button>
    </form>
    
    <div class="mt-3 text-center">
        <a href="/modular-store/modules/catalog/views/list.php" class="text-muted">← Back to Store</a>
    </div>
</div>
</body>
</html>