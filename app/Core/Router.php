<?php
declare(strict_types=1);

namespace Skoolyst\Core;

class Router {
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void {}
    public function post(string $path, callable|array $handler, array $middleware = []): void {}
    public function put(string $path, callable|array $handler, array $middleware = []): void {}
    public function delete(string $path, callable|array $handler, array $middleware = []): void {}
    public function dispatch(string $method, string $uri): mixed { return null; }
}
