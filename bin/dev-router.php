<?php
/**
 * Local-testing-only router for `php -S`, mirroring public/.htaccess:
 * serve existing static files (public/assets/...) directly, otherwise
 * hand off to public/index.php. Not used in production (Apache/Nginx
 * handle this via .htaccess / server config there).
 *
 * Usage: php -S 127.0.0.1:8000 -t public bin/dev-router.php
 */
$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = __DIR__ . '/../public' . $path;

if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server serve the static file as-is
}

require __DIR__ . '/../public/index.php';
