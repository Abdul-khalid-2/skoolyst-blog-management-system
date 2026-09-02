<?php
declare(strict_types=1);

namespace Skoolyst\Core;

class Response {
    public static function json(mixed $data, int $status = 200): never {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $url, int $status = 302): never {
        header('Location: ' . $url, true, $status);
        exit;
    }
}
