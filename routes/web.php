<?php
declare(strict_types=1);

use Skoolyst\Controllers\AuthController;
use Skoolyst\Controllers\CategoryController;
use Skoolyst\Controllers\CommentController;
use Skoolyst\Controllers\MediaController;
use Skoolyst\Controllers\PageController;
use Skoolyst\Controllers\PostController;
use Skoolyst\Core\Router;

$router = new Router();

// admin.php registers its routes onto this same $router instance (shared via
// the `require` scope, same trick resources/views layouts use for $content) —
// keeps a single Router/dispatch call in public/index.php.
require __DIR__ . '/admin.php';
require __DIR__ . '/api.php';

$router->get('/', [PostController::class, 'home']);
$router->get('/blog', [PostController::class, 'index']);
$router->get('/category/{slug}', [CategoryController::class, 'show']);
$router->get('/post/{slug}', [PostController::class, 'show']);
$router->post('/post/{slug}/comments', [CommentController::class, 'store']);
$router->get('/about', [PageController::class, 'about']);
$router->get('/contact', [PageController::class, 'contact']);
$router->post('/contact', [PageController::class, 'submitContact']);
$router->post('/newsletter', [PageController::class, 'newsletter']);
$router->get('/media/{filename}', [MediaController::class, 'serve']);

$router->get('/login', [AuthController::class, 'showLogin'], ['Guest']);
$router->post('/login', [AuthController::class, 'login'], ['Guest']);
$router->get('/signup', [AuthController::class, 'showSignup'], ['Guest']);
$router->post('/signup', [AuthController::class, 'signup'], ['Guest']);
$router->get('/logout', [AuthController::class, 'logout'], ['Auth']);

return $router;
