<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use RuntimeException;
use Skoolyst\Core\Session;
use Skoolyst\Models\User;

/**
 * "Continue with Google" — this app's own Google OAuth2 authorization-code client
 * (independent of "Login with Skoolyst"). Sits alongside password + Skoolyst login.
 *
 * Account matching, in order:
 *  1. A local account already linked to this Google id (`sub`) → sign in.
 *  2. A local account with the same email → linked and signed in ONLY when Google
 *     reports the email as verified; refused if it's linked to a different Google
 *     account or the email is unverified.
 *  3. Nobody → a new 'reader' account is created (an admin can promote it later).
 */
class GoogleAuthService {
    private const AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';
    private const STATE_TTL = 600;
    private const NEW_USER_ROLE = 'reader';

    private array $config;

    public function __construct(private AuthService $auth = new AuthService()) {
        $this->config = require dirname(__DIR__, 2) . '/config/google.php';
    }

    public function isConfigured(): bool {
        return $this->config['client_id'] !== '' && $this->config['client_secret'] !== '';
    }

    public function authorizeUrl(): string {
        $state = bin2hex(random_bytes(16));
        Session::put('_google_state', ['value' => $state, 'at' => time()]);

        return self::AUTHORIZE_URL . '?' . http_build_query([
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]);
    }

    /**
     * Verify `state`, exchange the code server-to-server, then fetch the profile.
     * @return array{id:string, name:string, email:string, email_verified:bool}
     * @throws RuntimeException with a message safe to show the user.
     */
    public function handleCallback(string $code, string $state): array {
        $expected = Session::get('_google_state');
        Session::forget('_google_state');

        if (!is_array($expected) || $state === '' || !hash_equals((string) $expected['value'], $state)) {
            throw new RuntimeException('Your Google sign-in could not be verified. Please try again.');
        }
        if (time() - (int) $expected['at'] > self::STATE_TTL) {
            throw new RuntimeException('Your Google sign-in took too long. Please try again.');
        }
        if ($code === '') {
            throw new RuntimeException('Google did not return a sign-in code. Please try again.');
        }

        [$status, $token] = $this->request(self::TOKEN_URL, http_build_query([
            'code' => $code,
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'redirect_uri' => $this->config['redirect_uri'],
            'grant_type' => 'authorization_code',
        ]));
        if ($status !== 200 || empty($token['access_token'])) {
            $this->log("Token exchange failed (HTTP {$status}): " . json_encode(['error' => $token['error'] ?? null, 'description' => $token['error_description'] ?? null]));
            throw new RuntimeException(($token['error'] ?? '') === 'invalid_client'
                ? 'Continue with Google is misconfigured on this site. Please use another sign-in option for now.'
                : 'Could not sign you in with Google right now. Please try again.');
        }

        [$status, $profile] = $this->request(self::USERINFO_URL, null, $token['access_token']);
        if ($status !== 200 || empty($profile['sub']) || empty($profile['email'])) {
            $this->log("Userinfo request failed (HTTP {$status})");
            throw new RuntimeException('Could not read your Google profile. Please try again.');
        }

        $email = strtolower(trim((string) $profile['email']));
        return [
            'id' => (string) $profile['sub'],
            'name' => trim((string) ($profile['name'] ?? '')) ?: strstr($email, '@', true),
            'email' => $email,
            'email_verified' => filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /** @return array{status:'logged_in', user:array, created:bool}|array{status:'error', message:string} */
    public function resolve(array $identity): array {
        if ($user = User::findByGoogleId($identity['id'])) {
            return $this->signIn($user);
        }

        if ($existing = User::findByEmail($identity['email'])) {
            if (!empty($existing['google_id'])) {
                return ['status' => 'error', 'message' => 'This email is already linked to a different Google account. Sign in with your email and password instead.'];
            }
            if (!$identity['email_verified']) {
                return ['status' => 'error', 'message' => 'An account with this email already exists here. Please sign in with your password.'];
            }
            User::update((int) $existing['id'], ['google_id' => $identity['id']]);
            return $this->signIn(User::findById((int) $existing['id']));
        }

        $id = User::createFromGoogle($identity['id'], mb_substr($identity['name'], 0, 120), $identity['email'], self::NEW_USER_ROLE);
        $user = User::findById($id);
        $this->auth->startSession($user);
        (new NotificationService())->userRegistered($user);
        return ['status' => 'logged_in', 'user' => $user, 'created' => true];
    }

    private function signIn(array $user): array {
        if ((int) ($user['active'] ?? 1) === 0) {
            return ['status' => 'error', 'message' => 'This account has been deactivated. Contact an administrator.'];
        }
        $this->auth->startSession($user);
        return ['status' => 'logged_in', 'user' => $user, 'created' => false];
    }

    /** POST $form (url-encoded) when given, otherwise GET with a bearer token. @return array{0:int, 1:array} */
    private function request(string $url, ?string $form, ?string $bearer = null): array {
        $ch = curl_init($url);
        $headers = ['Accept: application/json'];
        if ($bearer !== null) $headers[] = 'Authorization: Bearer ' . $bearer;
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
        ]);
        if ($form !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $form);
        }
        $raw = curl_exec($ch);
        if ($raw === false) $this->log('Transport error: ' . curl_error($ch));
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        return [$status, is_array($decoded) ? $decoded : []];
    }

    private function log(string $message): void {
        error_log('[' . date('Y-m-d H:i:s') . "] [google] {$message}\n", 3, dirname(__DIR__, 2) . '/storage/logs/app.log');
    }
}
