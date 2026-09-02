<?php
declare(strict_types=1);

namespace Skoolyst\Middleware;

/**
 * API headers, authentication, rate-limit hooks and JSON-only responses.
 * TODO (Phase 8 — API): add token/session auth checks and rate limiting here.
 */
class ApiMiddleware {
    public static function handle(array $params = []): bool {
        header('Content-Type: application/json; charset=utf-8');
        return true;
    }
}
