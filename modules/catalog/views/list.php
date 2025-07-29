<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';
require_once __DIR__ . '/../../admin/helpers/auth.php';

$products = \Database::view('v_products');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/catalog/views/list.php">Modular Store</a>
        <div>
            <a class="btn btn-outline-light btn-sm me-2" href="/modular-store/modules/cart/controllers/view.php">Cart</a>
            <?php if (isAdmin()): ?>
                <a class="btn btn-outline-success btn-sm" href="/modular-store/modules/admin/controllers/dashboard.php">Admin Panel</a>
            <?php else: ?>
                <a class="btn btn-outline-secondary btn-sm" href="/modular-store/modules/admin/controllers/login.php">Admin</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="mb-4">Product Catalog</h1>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="position-relative">
                <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
                <div id="searchResults" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" style="z-index: 1000; display: none; max-height: 300px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>

    <div id="productGrid">
        <?php if (!$products): ?>
            <div class="alert alert-warning">No products available.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($products as $p): ?>
                    <div class="col">
                        <div class="card h-100">
                            <?php if ($p['image_url']): ?>
                                <img src="<?= htmlspecialchars($p['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($p['category']) ?></p>
                                <p class="fw-bold">$<?= number_format((float)$p['price'], 2) ?></p>
                                <form action="/modular-store/modules/cart/controllers/add.php" method="post">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <button class="btn btn-primary btn-sm">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const productGrid = document.getElementById('productGrid');
    let searchTimeout;
    let allProducts = <?= json_encode($products) ?>;

    function renderProducts(products) {
        if (!products.length) {
            productGrid.innerHTML = '<div class="alert alert-warning">No products found.</div>';
            return;
        }

        const html = `
            <div class="row row-cols-1 row-cols-md-3 g-4">
                ${products.map(p => `
                    <div class="col">
                        <div class="card h-100">
                            ${p.image_url ? `<img src="${p.image_url}" class="card-img-top" alt="${p.name}">` : ''}
                            <div class="card-body">
                                <h5 class="card-title">${p.name}</h5>
                                <p class="card-text text-muted">${p.category}</p>
                                <p class="fw-bold">$${parseFloat(p.price).toFixed(2)}</p>
                                <form action="/modular-store/modules/cart/controllers/add.php" method="post">
                                    <input type="hidden" name="product_id" value="${p.id}">
                                    <button class="btn btn-primary btn-sm">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        productGrid.innerHTML = html;
    }

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            renderProducts(allProducts);
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`/modular-store/public/api/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(products => {
                    renderProducts(products);
                    
                    if (products.length > 0) {
                        const resultsHtml = products.slice(0, 5).map(p => `
                            <div class="p-2 border-bottom search-item" style="cursor: pointer;" data-name="${p.name}">
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-2">${p.category}</small>
                                    <span>${p.name}</span>
                                    <span class="ms-auto fw-bold">$${parseFloat(p.price).toFixed(2)}</span>
                                </div>
                            </div>
                        `).join('');
                        
                        searchResults.innerHTML = resultsHtml;
                        searchResults.style.display = 'block';
                        
                        document.querySelectorAll('.search-item').forEach(item => {
                            item.addEventListener('click', function() {
                                searchInput.value = this.dataset.name;
                                searchResults.style.display = 'none';
                            });
                        });
                    } else {
                        searchResults.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    searchResults.style.display = 'none';
                });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
});
</script>
</body>
</html>