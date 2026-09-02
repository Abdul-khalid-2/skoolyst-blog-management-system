<?php
declare(strict_types=1);

namespace Skoolyst\Middleware;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;

/**
 * Protect administrator-only routes.
 * TODO (Phase 4 — Auth & Security): check the authenticated user's role
 * against the module's admin role(s) once AuthService/User roles are wired up.
 */
class AdminMiddleware {
    public static function handle(array $params = []): bool {
        if (!is_authenticated()) {
            if (Request::wantsJson()) {
                Response::json(['error' => 'Unauthenticated'], 401);
            }
            Response::redirect(url('/login'));
        }

        return true;
    }
}
