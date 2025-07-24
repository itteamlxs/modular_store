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
        $this->routes[] = [$method, $path, $handler];
    }

    public function dispatch(): void
    {
        [$method, $path] = [$_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)];

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($method === $routeMethod && $path === $routePath) {
                $handler();
                return;
            }
        }
        http_response_code(404);
        echo 'Not Found';
    }
}