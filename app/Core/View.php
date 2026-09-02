<?php
declare(strict_types=1);

namespace Skoolyst\Core;

class View {
    /**
     * Render a view file. When $layout is given, the view's own output is
     * captured and passed to the layout as $content (with the rest of $data
     * still in scope, e.g. $title for <title>/the admin page heading).
     */
    public static function render(string $view, array $data = [], ?string $layout = null): void {
        extract($data);
        $file = self::path($view);
        if (!is_file($file)) throw new \RuntimeException("View not found: {$view}");

        if ($layout === null) {
            require $file;
            return;
        }

        ob_start();
        require $file;
        $content = ob_get_clean();

        $layoutFile = dirname(__DIR__, 2) . '/resources/views/layouts/' . ltrim($layout, '/') . '.php';
        if (!is_file($layoutFile)) throw new \RuntimeException("Layout not found: {$layout}");
        require $layoutFile;
    }

    private static function path(string $view): string {
        return dirname(__DIR__, 2) . '/resources/views/' . ltrim($view, '/') . '.php';
    }
}
