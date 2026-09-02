<?php
declare(strict_types=1);

namespace Skoolyst\Middleware;

use Skoolyst\Core\Response;

/** Restrict authenticated users from guest-only routes (e.g. the login page). */
class GuestMiddleware {
    public static function handle(array $params = []): bool {
        if (!is_authenticated()) {
            return true;
        }

        Response::redirect(url('/dashboard'));
    }
}
