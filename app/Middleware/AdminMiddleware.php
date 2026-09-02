<?php
declare(strict_types=1);

namespace Skoolyst\Middleware;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;

/** Protect routes restricted to the 'admin' role specifically (e.g. user/settings management). */
class AdminMiddleware {
    public static function handle(array $params = []): bool {
        $user = auth_user();

        if (!$user) {
            if (Request::wantsJson()) {
                Response::json(['error' => 'Unauthenticated'], 401);
            }
            Response::redirect(url('/login'));
        }

        if (($user['role'] ?? null) !== 'admin') {
            if (Request::wantsJson()) {
                Response::json(['error' => 'Forbidden'], 403);
            }
            flash('error', "You don't have permission to do that.");
            Response::redirect(url('/dashboard'));
        }

        return true;
    }
}
