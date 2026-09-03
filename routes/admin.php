<?php
declare(strict_types=1);

// Admin panel routes. Registered onto the shared $router from routes/web.php
// (which requires this file) — see the note there for why.

use Skoolyst\Controllers\CategoryController;
use Skoolyst\Controllers\DashboardController;
use Skoolyst\Controllers\MediaController;
use Skoolyst\Controllers\PostController;

$router->get('/dashboard', [DashboardController::class, 'index'], ['Auth']);

$router->get('/dashboard/posts', [PostController::class, 'adminIndex'], ['Auth']);
$router->get('/dashboard/posts/create', [PostController::class, 'create'], ['Auth']);
$router->post('/dashboard/posts', [PostController::class, 'store'], ['Auth']);
$router->get('/dashboard/posts/{id}/edit', [PostController::class, 'edit'], ['Auth']);
$router->post('/dashboard/posts/{id}', [PostController::class, 'update'], ['Auth']);
$router->post('/dashboard/posts/{id}/delete', [PostController::class, 'destroy'], ['Auth']);

$router->get('/dashboard/categories', [CategoryController::class, 'adminIndex'], ['Auth']);
$router->post('/dashboard/categories', [CategoryController::class, 'store'], ['Auth']);
$router->post('/dashboard/categories/{id}', [CategoryController::class, 'update'], ['Auth']);
$router->post('/dashboard/categories/{id}/delete', [CategoryController::class, 'destroy'], ['Auth']);

$router->get('/dashboard/media', [MediaController::class, 'index'], ['Auth']);
$router->post('/dashboard/media', [MediaController::class, 'upload'], ['Auth']);
$router->post('/dashboard/media/{id}/delete', [MediaController::class, 'destroy'], ['Auth']);
