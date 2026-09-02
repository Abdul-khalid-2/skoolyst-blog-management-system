<?php
declare(strict_types=1);

namespace Skoolyst\Core;

class Session {
    public static function start(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name($_ENV['SESSION_NAME'] ?? 'skoolyst_module_session');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'httponly' => filter_var($_ENV['SESSION_HTTP_ONLY'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function get(string $key, mixed $default = null): mixed { return $_SESSION[$key] ?? $default; }

    public static function put(string $key, mixed $value): void { $_SESSION[$key] = $value; }

    public static function has(string $key): bool { return isset($_SESSION[$key]); }

    public static function forget(string $key): void { unset($_SESSION[$key]); }

    public static function regenerate(): void { session_regenerate_id(true); }

    public static function destroy(): void {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
