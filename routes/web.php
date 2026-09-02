<?php
declare(strict_types=1);

use Skoolyst\Core\Router;
use Skoolyst\Core\View;

$router = new Router();

// Temporary closures for Phase 3 (Shared UI System) verification.
// Replaced by real Controllers/Services in Phase 6 — Services & Controllers.
$router->get('/', function () {
    View::render('frontend/home', [], 'frontend');
});

$router->get('/login', function () {
    View::render('auth/login', [], 'auth');
}, ['Guest']);

$router->get('/dashboard', function () {
    View::render('admin/dashboard', [], 'admin');
}, ['Auth']);

return $router;
