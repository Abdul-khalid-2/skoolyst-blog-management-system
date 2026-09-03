<?php
declare(strict_types=1);

namespace Skoolyst\Core;

/**
 * Minimal PSR-inspired router.
 *
 * Supports static and {param} dynamic segments, per-route middleware
 * (resolved to Skoolyst\Middleware\{Name}Middleware::handle()), and both
 * closure and [ControllerClass, 'method'] handlers.
 */
class Router {
    /** @var array<string, array<int, array{pattern:string, keys:array<int,string>, handler:callable|array, middleware:array<int,string>}>> */
    private array $routes = [
        'GET' => [], 'POST' => [], 'PUT' => [], 'PATCH' => [], 'DELETE' => [],
    ];

    public function get(string $path, callable|array $handler, array $middleware = []): void {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): void {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|array $handler, array $middleware = []): void {
        $this->add('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, callable|array $handler, array $middleware): void {
        $path = '/' . trim($path, '/');
        $keys = [];
        $pattern = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function (array $m) use (&$keys) {
            $keys[] = $m[1];
            return '([^/]+)';
        }, $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'keys' => $keys,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * Match the request against registered routes and invoke the handler.
     * Runs middleware (in order) before the handler; any middleware that
     * returns false halts the chain (the middleware is responsible for
     * sending its own redirect/response before returning false).
     */
    public function dispatch(string $method, string $uri): mixed {
        $method = strtoupper($method);
        $path = '/' . trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
        if ($path === '') $path = '/';

        foreach ($this->routes[$method] ?? [] as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }
            array_shift($matches);
            $params = array_combine($route['keys'], $matches) ?: [];

            foreach ($route['middleware'] as $name) {
                $class = str_contains($name, '\\') ? $name : 'Skoolyst\\Middleware\\' . $name . 'Middleware';
                if (!class_exists($class) || !method_exists($class, 'handle')) {
                    continue;
                }
                if ($class::handle($params) === false) {
                    return null;
                }
            }

            // CSRF protection for state-changing web requests. Routes tagged with the
            // 'Api' middleware are exempt here — token/session auth for the JSON API
            // is handled by ApiMiddleware itself (Phase 8).
            if (!in_array($method, ['GET', 'HEAD'], true) && !in_array('Api', $route['middleware'], true)) {
                if (!csrf_verify()) {
                    return $this->csrfFailed();
                }
            }

            return $this->invoke($route['handler'], $params);
        }

        return $this->notFound();
    }

    private function invoke(callable|array $handler, array $params): mixed {
        // Route params come from preg_match as strings; auto-cast purely
        // numeric ones to int so typed `int $id` handler params work under
        // strict_types without every Controller having to cast manually.
        $params = array_map(fn ($v) => ctype_digit($v) ? (int) $v : $v, $params);

        if (is_array($handler)) {
            [$class, $method] = $handler;
            $instance = is_object($class) ? $class : new $class();
            return $instance->{$method}(...array_values($params));
        }
        return $handler(...array_values($params));
    }

    private function notFound(): mixed {
        http_response_code(404);
        if (Request::wantsJson()) {
            return Response::json(['error' => 'Not Found'], 404);
        }
        return View::render('errors/404', [], 'frontend');
    }

    private function csrfFailed(): mixed {
        http_response_code(419);
        if (Request::wantsJson()) {
            return Response::json(['error' => 'Invalid or expired security token. Please refresh and try again.'], 419);
        }
        flash('error', 'Your session expired. Please try again.');
        return Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}
