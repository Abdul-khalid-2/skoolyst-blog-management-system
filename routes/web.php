<?php
declare(strict_types=1);

use Skoolyst\Controllers\AuthController;
use Skoolyst\Core\Router;
use Skoolyst\Core\View;

$router = new Router();

$router->get('/', function () {
    View::render('frontend/home', [], 'frontend');
});

$router->get('/login', [AuthController::class, 'showLogin'], ['Guest']);
$router->post('/login', [AuthController::class, 'login'], ['Guest']);
$router->get('/logout', [AuthController::class, 'logout'], ['Auth']);

// Temporary closure — replaced by DashboardController in Phase 6 — Services & Controllers.
$router->get('/dashboard', function () {
    View::render('admin/dashboard', [], 'admin');
}, ['Auth']);

return $router;
