<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        // Normalizar la ruta - remover trailing slash excepto para root
        $normalizedPath = $path === '/' ? '/' : rtrim($path, '/');
        $this->routes[] = [$method, $normalizedPath, $handler];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Extraer solo el path, sin query string
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Normalizar el path - remover trailing slash excepto para root
        $normalizedPath = $path === '/' ? '/' : rtrim($path, '/');
        
        // Debug: mostrar la ruta que se está buscando (solo en desarrollo)
        if ($_ENV['APP_ENV'] === 'development') {
            error_log("Router: Looking for route: $method $normalizedPath");
        }
        
        // Buscar coincidencia exacta primero
        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($method === $routeMethod && $normalizedPath === $routePath) {
                try {
                    if ($_ENV['APP_ENV'] === 'development') {
                        error_log("Router: Found matching route: $routeMethod $routePath");
                    }
                    $handler();
                    return;
                } catch (Exception $e) {
                    http_response_code(500);
                    if ($_ENV['APP_ENV'] === 'development') {
                        echo "Error: " . $e->getMessage() . "<br>";
                        echo "File: " . $e->getFile() . " Line: " . $e->getLine();
                    } else {
                        echo 'Internal Server Error';
                    }
                    return;
                }
            }
        }
        
        // Debug: mostrar todas las rutas registradas
        if ($_ENV['APP_ENV'] === 'development') {
            error_log("Router: No matching route found. Available routes:");
            foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
                error_log("  $routeMethod $routePath");
            }
        }
        
        // Si no se encuentra, mostrar 404
        http_response_code(404);
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>404 - Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 text-center">
        <h1 class="display-1">404</h1>
        <h2>Page Not Found</h2>
        <p class="lead">The requested URL "' . htmlspecialchars($normalizedPath) . '" was not found on this server.</p>
        <a href="/modular-store" class="btn btn-primary">Go Home</a>
        ' . ($_ENV['APP_ENV'] === 'development' ? '<div class="mt-4"><small>Available routes:<br>' . implode('<br>', array_map(fn($r) => $r[0] . ' ' . $r[1], $this->routes)) . '</small></div>' : '') . '
    </div>
</body>
</html>';
    }
}