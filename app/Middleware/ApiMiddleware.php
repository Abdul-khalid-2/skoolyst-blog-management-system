<?php
declare(strict_types=1);

namespace Skoolyst\Middleware;

use Skoolyst\Core\Response;
use Skoolyst\Models\ApiKey;

/**
 * API headers + Bearer-token authentication against blog_api_keys.
 * Note: real rate limiting isn't implemented (would need a request-count
 * column/window, which blog_api_keys doesn't have yet) — flagged as a
 * follow-up if traffic volume ever makes it necessary.
 */
class ApiMiddleware {
    public static function handle(array $params = []): bool {
        header('Content-Type: application/json; charset=utf-8');

        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            Response::json(['error' => 'Missing or malformed Authorization header. Expected: Bearer <api_key>'], 401);
        }

        $apiKeys = new ApiKey();
        $key = $apiKeys->findActiveByHash(hash('sha256', $matches[1]));
        if (!$key) {
            Response::json(['error' => 'Invalid or revoked API key'], 401);
        }

        $apiKeys->touchLastUsed((int) $key['id']);
        return true;
    }
}
