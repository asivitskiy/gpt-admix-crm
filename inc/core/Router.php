<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void { $this->map('GET', $path, $handler); }
    public function post(string $path, $handler): void { $this->map('POST', $path, $handler); }

    private function map(string $method, string $path, $handler): void
    {
        $path = rtrim($path, '/');
        if ($path === '') $path = '/';
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(Request $req, array $c): string
    {
        $handler = $this->routes[$req->method][$req->path] ?? null;

        if (!$handler) {
            http_response_code(404);
            return '<div style="padding:20px;font-family:system-ui">404 Not Found: '.Helpers::h($req->path).'</div>';
        }

        return (string)$handler($req, $c);
    }
}
