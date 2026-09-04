<?php
declare(strict_types=1);

namespace Skoolyst\Middleware;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;

/**
 * Protect the dashboard: any logged-in staff account (admin/editor/author)
 * may enter — 'reader' accounts are public-site-only and get redirected home,
 * same pattern as AdminMiddleware but for the staff/reader split instead of admin-only.
 */
class StaffMiddleware {
    private const STAFF_ROLES = ['admin', 'editor', 'author'];

    public static function handle(array $params = []): bool {
        $user = auth_user();

        if (!$user) {
            if (Request::wantsJson()) {
                Response::json(['error' => 'Unauthenticated'], 401);
            }
            Response::redirect(url('/login'));
        }

        if (!in_array($user['role'] ?? null, self::STAFF_ROLES, true)) {
            if (Request::wantsJson()) {
                Response::json(['error' => 'Forbidden'], 403);
            }
            flash('error', 'The dashboard is for staff accounts only.');
            Response::redirect(url('/'));
        }

        return true;
    }
}
