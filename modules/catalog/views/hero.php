<?php
// modules/catalog/views/hero.php

// Configuración de imagen de fondo (opcional)
$heroImage = '/modular-store/assets/images/hero-bg.jpg'; // Cambia por tu imagen
$useBackgroundImage = file_exists($_SERVER['DOCUMENT_ROOT'] . $heroImage); // Verifica si existe la imagen

// Configuración de colores del texto (CAMBIA ESTOS SEGÚN TU IMAGEN)
$textColor = $useBackgroundImage ? '#ffffff' : '#1a1a1a';     // Blanco para fondo oscuro, negro para fondo claro
$mutedTextColor = $useBackgroundImage ? 'rgba(255,255,255,0.8)' : '#6c757d'; // Blanco semi-transparente o gris
$overlayColor = $useBackgroundImage ? 'rgba(0,0,0,0.4)' : 'transparent';      // Overlay oscuro opcional
?>
<div class="hero-section position-relative" 
     style="min-height: 50vh; 
            background: <?= $useBackgroundImage ? "linear-gradient({$overlayColor}, {$overlayColor}), url('{$heroImage}') center/cover no-repeat" : '#f8f9fa' ?>;">
    <div class="container-fluid h-100">
        <div class="d-flex flex-column justify-content-center align-items-start h-100 p-5">
            <!-- Texto pequeño arriba a la izquierda -->
            <div class="align-self-start mb-4">
                <h1 class="display-6 fw-bold mb-2" style="color: <?= $textColor ?>; letter-spacing: -0.02em;">TIENDA</h1>
                <p class="mb-0" style="font-size: 0.9rem; line-height: 1.4; color: <?= $mutedTextColor ?>;">
                    Norsk design laget av<br>
                    100% resirkulert plast
                </p>
            </div>
            
            <!-- Texto grande centrado -->
            <div class="align-self-center text-center">
                <h2 class="display-1 fw-bold m-0" style="color: <?= $textColor ?>; font-size: clamp(6rem, 15vw, 12rem); letter-spacing: -0.08em; line-height: 0.85; font-weight: 900;">
                    SILK HERO
                </h2>
            </div>
        </div>
        
        <!-- Botón flotante en la esquina inferior derecha -->
        <div class="position-absolute bottom-0 end-0 p-4">
            <a href="#products" class="btn rounded-circle d-flex align-items-center justify-content-center" 
               style="width: 60px; height: 60px; text-decoration: none; background-color: <?= $useBackgroundImage ? 'rgba(255,255,255,0.9)' : '#1a1a1a' ?>; color: <?= $useBackgroundImage ? '#1a1a1a' : '#ffffff' ?>;">
                <i class="fas fa-arrow-down"></i>
            </a>
        </div>
    </div>
</div>

<style>
.hero-section {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.hero-section .btn:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

@media (max-width: 991px) {
    .hero-section {
        min-height: 40vh;
    }
    
    .hero-section .display-6 {
        font-size: 2.5rem;
    }
}

@media (max-width: 576px) {
    .hero-section {
        min-height: 35vh;
    }
    
    .hero-section .p-5 {
        padding: 2rem !important;
    }
    
    .hero-section .display-6 {
        font-size: 2rem;
    }
    
    .hero-section p {
        font-size: 0.8rem !important;
    }
}
</style>