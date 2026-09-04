<?php
declare(strict_types=1);
require dirname(__DIR__) . '/bootstrap/app.php';

use Skoolyst\Core\Session;
use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\View;

Session::start();

// Web front controller. Admin/API surfaces get their own entry points
// once Phase 6+ splits admin.php / api.php onto dedicated front controllers
// or path-prefix dispatch.
$router = require dirname(__DIR__) . '/routes/web.php';

try {
    $router->dispatch(Request::method(), Request::uri());
} catch (\Throwable $e) {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . "\n", 3, dirname(__DIR__) . '/storage/logs/app.log');
    http_response_code(500);
    if (Request::wantsJson()) {
        Response::json(['error' => 'Server error'], 500);
    } else {
        View::render('errors/500', [], 'frontend');
    }
}
