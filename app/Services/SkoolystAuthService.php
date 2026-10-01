<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use RuntimeException;
use Skoolyst\Core\Session;
use Skoolyst\Models\User;

/**
 * "Login with Skoolyst" — OAuth2 authorization-code client for skoolyst.com, the
 * central identity provider for the Skoolyst app family. Sits alongside the normal
 * email/password login (AuthService), which keeps working unchanged.
 *
 * Account matching, in order:
 *  1. A local account already linked to this Skoolyst user id → sign in.
 *  2. A local account with the same email → linked and signed in ONLY when skoolyst.com
 *     reports the email as verified (an unverified email on skoolyst.com must not be
 *     able to take over an existing blog account). If not verified yet, the user is
 *     sent to skoolyst.com's /oauth/verify-required instead — it emails them a
 *     verification link that returns them here, automatically, once clicked.
 *  3. Nobody → the identity is parked in the session and the user picks author/reader
 *     once (mirrors the signup form), then the account is created.
 */
class SkoolystAuthService {
    private const STATE_TTL = 600;   // seconds the user has to finish on skoolyst.com
    private const PENDING_TTL = 900; // seconds to pick a role after a first-time SSO login
    private const PUBLIC_ROLES = ['author', 'reader'];

    private array $config;

    public function __construct(private AuthService $auth = new AuthService()) {
        $this->config = require dirname(__DIR__, 2) . '/config/sso.php';
    }

    public function isConfigured(): bool {
        return $this->config['client_id'] !== '' && $this->config['client_secret'] !== '' && $this->config['base_url'] !== '';
    }

    /** Step 2: where to send the browser. Stores a fresh `state` in the session for the CSRF check on the way back. */
    public function authorizeUrl(): string {
        return $this->config['base_url'] . '/oauth/authorize?' . http_build_query([
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'state' => $this->freshState(),
        ]);
    }

    /**
     * Sent to when resolve() finds a local account with this email that it
     * can't safely auto-link (email not verified on skoolyst.com yet).
     * skoolyst.com emails the user a verification link there; once clicked,
     * it sends the browser back to /oauth/authorize with these same
     * client_id/redirect_uri/state, which lands them back on our callback
     * with a fresh code — finishing the login they started here.
     */
    public function verifyRequiredUrl(): string {
        return $this->config['base_url'] . '/oauth/verify-required?' . http_build_query([
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'state' => $this->freshState(),
        ]);
    }

    /** Generates a fresh CSRF `state` and stores it for the callback's check. */
    private function freshState(): string {
        $state = bin2hex(random_bytes(16));
        Session::put('_sso_state', ['value' => $state, 'at' => time()]);
        return $state;
    }

    /**
     * Steps 3–4: verify `state`, then exchange the one-time code server-to-server.
     * @return array{id:int, name:string, email:string, email_verified:bool}
     * @throws RuntimeException with a message safe to show the user.
     */
    public function handleCallback(string $code, string $state): array {
        $expected = Session::get('_sso_state');
        Session::forget('_sso_state'); // single use, whatever happens next

        if (!is_array($expected) || $state === '' || !hash_equals((string) $expected['value'], $state)) {
            throw new RuntimeException('Your Skoolyst sign-in could not be verified. Please try again.');
        }
        if (time() - (int) $expected['at'] > self::STATE_TTL) {
            throw new RuntimeException('Your Skoolyst sign-in took too long. Please try again.');
        }
        if ($code === '') {
            throw new RuntimeException('Skoolyst did not return a sign-in code. Please try again.');
        }

        [$status, $response] = $this->post('/api/oauth/token', [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'code' => $code,
            'redirect_uri' => $this->config['redirect_uri'],
        ]);

        $user = $response['data']['user'] ?? null;
        if ($status !== 201 || empty($response['success']) || !is_array($user) || empty($user['id']) || empty($user['email'])) {
            $this->log("Token exchange failed (HTTP {$status}): " . json_encode($response['error'] ?? $response));
            throw new RuntimeException(match ($response['error']['code'] ?? '') {
                'invalid_grant' => 'That Skoolyst sign-in link has expired. Please try again.',
                'invalid_client' => 'Login with Skoolyst is misconfigured on this site. Please use your email and password for now.',
                default => 'Could not sign you in with Skoolyst right now. Please try again in a moment.',
            });
        }

        return [
            'id' => (int) $user['id'],
            'name' => trim((string) ($user['name'] ?? '')) ?: strstr((string) $user['email'], '@', true),
            'email' => strtolower(trim((string) $user['email'])),
            'email_verified' => (bool) ($user['email_verified'] ?? false),
        ];
    }

    /**
     * Step 5: sign in / link / or park for role choice.
     * @return array{status:'logged_in', user:array}|array{status:'needs_role'}|array{status:'needs_verification', redirect_url:string}|array{status:'error', message:string}
     */
    public function resolve(array $identity): array {
        if ($user = User::findBySkoolystId($identity['id'])) {
            return $this->signIn($user);
        }

        if ($existing = User::findByEmail($identity['email'])) {
            if (!empty($existing['skoolyst_id'])) {
                return ['status' => 'error', 'message' => 'This email is already linked to a different Skoolyst account. Sign in with your email and password instead.'];
            }
            if (!$identity['email_verified']) {
                // Not an error — send the user to skoolyst.com to verify their
                // email; it emails them a link that, once clicked, redirects
                // back through /oauth/authorize and lands on our callback
                // again with a fresh code, finishing this login automatically.
                return ['status' => 'needs_verification', 'redirect_url' => $this->verifyRequiredUrl()];
            }
            User::update((int) $existing['id'], ['skoolyst_id' => $identity['id']]);
            return $this->signIn(User::findById((int) $existing['id']));
        }

        Session::put('_sso_pending', $identity + ['at' => time()]);
        return ['status' => 'needs_role'];
    }

    /** The parked first-time identity (see resolve()), or null if missing/expired. */
    public function pending(): ?array {
        $pending = Session::get('_sso_pending');
        if (!is_array($pending) || time() - (int) $pending['at'] > self::PENDING_TTL) {
            Session::forget('_sso_pending');
            return null;
        }
        return $pending;
    }

    /** Create the account for the parked identity with the chosen role, and sign in. Returns null on success or an error message. */
    public function completeSignup(string $role): ?string {
        $pending = $this->pending();
        if (!$pending) return 'Your Skoolyst sign-in expired. Please try again.';
        if (!in_array($role, self::PUBLIC_ROLES, true)) return 'Please choose an account type.';

        // Something may have changed while the role page was open (e.g. a second tab finished first).
        if (User::findBySkoolystId($pending['id']) || User::findByEmail($pending['email'])) {
            Session::forget('_sso_pending');
            $result = $this->resolve($pending);
            return $result['status'] === 'logged_in' ? null : ($result['message'] ?? 'Please sign in again.');
        }

        $id = User::createFromSkoolyst($pending['id'], $pending['name'], $pending['email'], $role);
        Session::forget('_sso_pending');
        $user = User::findById($id);
        $this->auth->startSession($user);
        (new NotificationService())->userRegistered($user);
        return null;
    }

    private function signIn(array $user): array {
        if ((int) ($user['active'] ?? 1) === 0) {
            return ['status' => 'error', 'message' => 'This account has been deactivated. Contact an administrator.'];
        }
        $this->auth->startSession($user);
        return ['status' => 'logged_in', 'user' => $user];
    }

    /** @return array{0:int, 1:array} */
    private function post(string $path, array $payload): array {
        $ch = curl_init($this->config['base_url'] . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 30,
        ]);
        $raw = curl_exec($ch);
        if ($raw === false) $this->log('Token request transport error: ' . curl_error($ch));
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        return [$status, is_array($decoded) ? $decoded : []];
    }

    private function log(string $message): void {
        error_log('[' . date('Y-m-d H:i:s') . "] [sso] {$message}\n", 3, dirname(__DIR__, 2) . '/storage/logs/app.log');
    }
}
