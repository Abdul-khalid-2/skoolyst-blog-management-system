<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Core\Session;
use Skoolyst\Models\User;

/**
 * Central authentication business logic. Keep controllers thin.
 */
class AuthService {
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 300;

    /** Roles self-service signup is allowed to create. Admin/editor stay internally-provisioned (seeder/CLI only). */
    private const PUBLIC_ROLES = ['author', 'reader'];

    /**
     * Register a new public-facing account and log it in immediately.
     * Returns null on success, or an error message to show the user on failure.
     */
    public function register(string $name, string $email, string $password, string $role): ?string {
        if (!in_array($role, self::PUBLIC_ROLES, true)) {
            return 'Invalid account type.';
        }
        if (User::findByEmail($email)) {
            return 'That email is already registered.';
        }

        $id = User::create($name, $email, $password, $role);
        $user = User::findById($id);

        Session::regenerate();
        Session::put('user', User::forSession($user));
        User::touchLastLogin($id);

        return null;
    }

    /**
     * Attempt to authenticate. Returns null on success, or an error message
     * to show the user on failure (also covers the lockout case).
     */
    public function attempt(string $email, string $password): ?string {
        if ($lockedFor = $this->lockedFor()) {
            return "Too many login attempts. Please try again in {$lockedFor} seconds.";
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->registerFailure();
            return 'Those credentials do not match our records.';
        }

        if ((int) ($user['active'] ?? 1) === 0) {
            return 'This account has been deactivated. Contact an administrator.';
        }

        $this->clearFailures();
        Session::regenerate();
        Session::put('user', User::forSession($user));
        User::touchLastLogin((int) $user['id']);

        return null;
    }

    public function logout(): void {
        Session::destroy();
    }

    private function registerFailure(): void {
        $attempts = (int) Session::get('_login_attempts', 0) + 1;
        Session::put('_login_attempts', $attempts);
        if ($attempts >= self::MAX_ATTEMPTS) {
            Session::put('_login_locked_until', time() + self::LOCKOUT_SECONDS);
        }
    }

    private function clearFailures(): void {
        Session::forget('_login_attempts');
        Session::forget('_login_locked_until');
    }

    /** Seconds remaining in the lockout, or null when not locked. */
    private function lockedFor(): ?int {
        $until = Session::get('_login_locked_until');
        if (!$until) return null;
        $remaining = (int) $until - time();
        if ($remaining <= 0) {
            $this->clearFailures();
            return null;
        }
        return $remaining;
    }
}
