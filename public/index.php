<?php
declare(strict_types=1);
require dirname(__DIR__) . '/bootstrap/app.php';

use Skoolyst\Core\Session;
use Skoolyst\Core\Request;

Session::start();

// Load route definitions here after implementing Router::dispatch().
require dirname(__DIR__) . '/routes/web.php';

// API/admin routing can be selected by the application's front controller strategy.
