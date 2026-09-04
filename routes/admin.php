<?php
declare(strict_types=1);

// Admin panel routes. Registered onto the shared $router from routes/web.php
// (which requires this file) — see the note there for why.

use Skoolyst\Controllers\CategoryController;
use Skoolyst\Controllers\DashboardController;
use Skoolyst\Controllers\MediaController;
use Skoolyst\Controllers\PostController;

$router->get('/dashboard', [DashboardController::class, 'index'], ['Staff']);

$router->get('/dashboard/posts', [PostController::class, 'adminIndex'], ['Staff']);
$router->get('/dashboard/posts/create', [PostController::class, 'create'], ['Staff']);
$router->post('/dashboard/posts', [PostController::class, 'store'], ['Staff']);
$router->get('/dashboard/posts/{id}/edit', [PostController::class, 'edit'], ['Staff']);
$router->post('/dashboard/posts/{id}', [PostController::class, 'update'], ['Staff']);
$router->post('/dashboard/posts/{id}/delete', [PostController::class, 'destroy'], ['Staff']);

$router->get('/dashboard/categories', [CategoryController::class, 'adminIndex'], ['Staff']);
$router->post('/dashboard/categories', [CategoryController::class, 'store'], ['Staff']);
$router->post('/dashboard/categories/{id}', [CategoryController::class, 'update'], ['Staff']);
$router->post('/dashboard/categories/{id}/delete', [CategoryController::class, 'destroy'], ['Staff']);

$router->get('/dashboard/media', [MediaController::class, 'index'], ['Staff']);
$router->post('/dashboard/media', [MediaController::class, 'upload'], ['Staff']);
$router->post('/dashboard/media/{id}/delete', [MediaController::class, 'destroy'], ['Staff']);
