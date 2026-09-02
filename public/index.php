<?php
declare(strict_types=1);
require dirname(__DIR__) . '/bootstrap/app.php';

use Skoolyst\Core\Session;
use Skoolyst\Core\Request;

Session::start();

// Web front controller. Admin/API surfaces get their own entry points
// once Phase 6+ splits admin.php / api.php onto dedicated front controllers
// or path-prefix dispatch.
$router = require dirname(__DIR__) . '/routes/web.php';
$router->dispatch(Request::method(), Request::uri());
