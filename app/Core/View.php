<?php
declare(strict_types=1);

namespace Skoolyst\Core;

class View {
    public static function render(string $view, array $data = []): void {
        extract($data);
        $file = dirname(__DIR__, 2) . '/resources/views/' . ltrim($view, '/') . '.php';
        if (!is_file($file)) throw new \RuntimeException("View not found: {$view}");
        require $file;
    }
}
