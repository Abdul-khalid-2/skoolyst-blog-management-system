<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Core\View;
use Skoolyst\Services\AuthService;
use Skoolyst\Services\SkoolystAuthService;

/**
 * Authentication UI/API entry points: login, logout, signup.
 *
 * Public signup creates 'author' or 'reader' accounts only — admin/editor stay
 * internally-provisioned (seeder/CLI), matching the dashboard's original design.
 * No forgot-password screen is built yet; add one in a later phase if needed.
 */
class AuthController {
    public function __construct(private AuthService $auth = new AuthService()) {}

    public function showLogin(): void {
        View::render('auth/login', [], 'auth');
    }

    public function login(): mixed {
        $errors = Validator::make(Request::all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($errors) {
            flash('error', 'Please fix the errors below and try again.');
            return View::render('auth/login', ['errors' => $errors], 'auth');
        }

        $failureMessage = $this->auth->attempt(
            (string) Request::input('email'),
            (string) Request::input('password')
        );

        if ($failureMessage !== null) {
            flash('error', $failureMessage);
            return View::render('auth/login', [], 'auth');
        }

        return Response::redirect(url('/dashboard'));
    }

    public function showSignup(): void {
        View::render('auth/signup', [], 'auth');
    }

    public function signup(): mixed {
        $errors = Validator::make(Request::all(), [
            'name' => 'required|max:120',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|confirmed:password',
            'role' => 'required|in:author,reader',
        ]);
        if (isset($errors['password_confirmation'])) {
            $errors['password_confirmation'] = ['Passwords do not match.'];
        }

        if ($errors) {
            flash('error', 'Please fix the errors below and try again.');
            return View::render('auth/signup', ['errors' => $errors], 'auth');
        }

        $failureMessage = $this->auth->register(
            (string) Request::input('name'),
            (string) Request::input('email'),
            (string) Request::input('password'),
            (string) Request::input('role')
        );

        if ($failureMessage !== null) {
            flash('error', $failureMessage);
            return View::render('auth/signup', [], 'auth');
        }

        $role = (string) Request::input('role');
        flash('success', 'Welcome to Skoolyst Blog!');
        return Response::redirect(url($role === 'reader' ? '/' : '/dashboard'));
    }

    // --- Login with Skoolyst (SkoolystAuthService) ---

    public function skoolystRedirect(): never {
        $sso = new SkoolystAuthService();
        if (!$sso->isConfigured()) {
            flash('error', 'Login with Skoolyst is not available right now. Please use your email and password.');
            Response::redirect(url('/login'));
        }
        Response::redirect($sso->authorizeUrl());
    }

    public function skoolystCallback(): mixed {
        $sso = new SkoolystAuthService();

        // User cancelled or skoolyst.com reported a problem before issuing a code.
        if (Request::query('error') !== null) {
            flash('error', 'Skoolyst sign-in was cancelled or failed. Please try again.');
            return Response::redirect(url('/login'));
        }

        try {
            $identity = $sso->handleCallback((string) Request::query('code', ''), (string) Request::query('state', ''));
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            return Response::redirect(url('/login'));
        }

        $result = $sso->resolve($identity);
        if ($result['status'] === 'logged_in') {
            $this->redirectAfterLogin((string) $result['user']['role']);
        }
        if ($result['status'] === 'needs_role') {
            Response::redirect(url('/auth/skoolyst/account-type'));
        }
        if ($result['status'] === 'needs_verification') {
            Response::redirect((string) $result['redirect_url']);
        }
        flash('error', $result['message']);
        return Response::redirect(url('/login'));
    }

    public function showSkoolystRole(): mixed {
        $pending = (new SkoolystAuthService())->pending();
        if (!$pending) {
            flash('error', 'Your Skoolyst sign-in expired. Please try again.');
            return Response::redirect(url('/login'));
        }
        return View::render('auth/skoolyst-role', ['pending' => $pending], 'auth');
    }

    public function completeSkoolystSignup(): mixed {
        $role = (string) Request::input('role', '');
        $failureMessage = (new SkoolystAuthService())->completeSignup($role);
        if ($failureMessage !== null) {
            flash('error', $failureMessage);
            return Response::redirect(url('/login'));
        }
        flash('success', 'Welcome to Skoolyst Blog!');
        return $this->redirectAfterLogin((string) (auth_user()['role'] ?? $role));
    }

    private function redirectAfterLogin(string $role): never {
        Response::redirect(url($role === 'reader' ? '/' : '/dashboard'));
    }

    public function logout(): never {
        $this->auth->logout();
        Response::redirect(url('/login'));
    }
}
