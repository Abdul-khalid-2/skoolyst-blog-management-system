<?php
declare(strict_types=1);

use Skoolyst\Core\Router;

$router = new Router();

// Boot-verification route (Phase 2). Real frontend routes/controllers land in Phase 3+.
$router->get('/', function () {
    echo 'Skoolyst Blog module booted successfully.';
});

return $router;
