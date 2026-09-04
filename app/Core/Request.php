<?php
declare(strict_types=1);

namespace Skoolyst\Core;

class Request {
    private static ?array $jsonBody = null;

    /** Real HTTP method, honoring a `_method` override field for PUT/PATCH/DELETE from HTML forms. */
    public static function method(): string {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }
        return $method;
    }

    /**
     * Request path, relative to the app's own mount point. Apps served from a
     * subdirectory (e.g. XAMPP htdocs/Projects/.../public) get a REQUEST_URI
     * that includes that prefix; the Router matches routes like `/blog`
     * against paths with it stripped, using APP_URL's own path as the prefix
     * (the same source of truth the url() helper uses to build links).
     */
    public static function uri(): string {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = rtrim(parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH) ?? '', '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        return $path === '' ? '/' : $path;
    }

    public static function isJson(): bool {
        return str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    }

    public static function wantsJson(): bool {
        return self::isJson() || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    /** Decoded JSON request body (cached), or [] when not a JSON request / invalid payload. */
    public static function jsonBody(): array {
        if (self::$jsonBody !== null) return self::$jsonBody;
        if (!self::isJson()) return self::$jsonBody = [];
        $raw = file_get_contents('php://input') ?: '';
        $decoded = json_decode($raw, true);
        return self::$jsonBody = is_array($decoded) ? $decoded : [];
    }

    public static function input(string $key, mixed $default = null): mixed {
        if (self::isJson()) {
            return self::jsonBody()[$key] ?? $default;
        }
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public static function query(string $key, mixed $default = null): mixed {
        return $_GET[$key] ?? $default;
    }

    public static function all(): array {
        return self::isJson() ? array_merge($_GET, self::jsonBody()) : array_merge($_GET, $_POST);
    }

    public static function only(array $keys): array {
        $all = self::all();
        return array_intersect_key($all, array_flip($keys));
    }
}
