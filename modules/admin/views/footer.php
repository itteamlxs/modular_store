<style>
body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

footer {
    padding:60px;
}

main, .container, .container-fluid {
    flex: 1;
}
</style>

<footer class="bg-dark text-light py-4 mt-auto">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 mb-3">
                <h6 class="fw-bold mb-2 text-white">
                    <i class="fas fa-cogs me-2"></i>
                    Modular Store Admin
                </h6>
                <p class="text-light mb-2">Panel de administración del sistema</p>
                <small class="text-light">
                    <i class="fas fa-shield-alt me-1"></i>
                    Acceso autorizado únicamente
                </small>
            </div>
            
            <div class="col-lg-4 mb-3">
                <h6 class="fw-bold mb-2 text-white">
                    <i class="fas fa-chart-bar me-2"></i>
                    Sistema
                </h6>
                <div class="row text-center">
                    <div class="col-4">
                        <small class="text-light d-block">PHP</small>
                        <small class="text-success"><?= phpversion() ?></small>
                    </div>
                    <div class="col-4">
                        <small class="text-light d-block">Uptime</small>
                        <small class="text-info">Online</small>
                    </div>
                    <div class="col-4">
                        <small class="text-light d-block">Status</small>
                        <small class="text-success">
                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i> OK
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-3">
                <h6 class="fw-bold mb-2 text-white">
                    <i class="fas fa-user-shield me-2"></i>
                    Sesión Activa
                </h6>
                <div class="bg-secondary p-2 rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-light fw-bold">
                                <?= $_SESSION['admin_name'] ?? 'Administrator' ?>
                            </small>
                            <br>
                            <small class="text-light">
                                <?= $_SESSION['admin_email'] ?? 'admin@store.com' ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <small class="text-light d-block">
                                <i class="fas fa-clock me-1"></i>
                                <?= date('H:i') ?>
                            </small>
                            <small class="text-light">
                                <?= date('d/m/Y') ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <hr class="border-secondary my-3">
        
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="text-light mb-0">
                    &copy; <?= date('Y') ?> Modular Store Admin Panel. 
                    <span class="text-success">v2.0</span>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-light">
                    <a href="#" class="text-decoration-none text-light me-3">
                        <i class="fas fa-question-circle me-1"></i>Help
                    </a>
                    <a href="#" class="text-decoration-none text-light">
                        <i class="fas fa-bug me-1"></i>Report Issue
                    </a>
                </small>
            </div>
        </div>
    </div>
</footer>