<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h6 class="fw-bold mb-2">Modular Store Admin</h6>
                <p class="text-muted mb-0">Panel de administración &copy; <?= date('Y') ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">
                    <i class="fas fa-user me-1"></i>
                    <?= $_SESSION['admin_name'] ?? 'Administrator' ?>
                    <span class="mx-2">|</span>
                    <i class="fas fa-clock me-1"></i>
                    <?= date('d/m/Y H:i') ?>
                </small>
            </div>
        </div>
    </div>
</footer>