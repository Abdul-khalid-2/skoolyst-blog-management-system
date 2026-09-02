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

    public static function uri(): string { return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; }

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
