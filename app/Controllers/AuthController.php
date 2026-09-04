<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Core\View;
use Skoolyst\Services\AuthService;

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

    public function logout(): never {
        $this->auth->logout();
        Response::redirect(url('/login'));
    }
}
