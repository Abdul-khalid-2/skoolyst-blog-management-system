<?php
declare(strict_types=1);

namespace Skoolyst\Middleware;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;

/** Protect authenticated web/API routes. */
class AuthMiddleware {
    public static function handle(array $params = []): bool {
        if (is_authenticated()) {
            return true;
        }

        if (Request::wantsJson()) {
            Response::json(['error' => 'Unauthenticated'], 401);
        }
        Response::redirect(url('/login'));
    }
}
