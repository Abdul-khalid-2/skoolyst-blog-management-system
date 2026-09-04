<?php
declare(strict_types=1);

// Versionable JSON API routes. Registered onto the shared $router from
// routes/web.php, same as routes/admin.php — see the note there.

use Skoolyst\Controllers\Api\CategoryController;
use Skoolyst\Controllers\Api\CommentController;
use Skoolyst\Controllers\Api\PostController;

$router->get('/api/v1/posts', [PostController::class, 'index'], ['Api']);
$router->get('/api/v1/posts/{slug}', [PostController::class, 'show'], ['Api']);
$router->post('/api/v1/posts/{slug}/comments', [CommentController::class, 'store'], ['Api']);
$router->get('/api/v1/categories', [CategoryController::class, 'index'], ['Api']);
$router->get('/api/v1/categories/{slug}', [CategoryController::class, 'show'], ['Api']);
