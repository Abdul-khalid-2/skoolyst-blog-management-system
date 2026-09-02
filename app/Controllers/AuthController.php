<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Core\View;
use Skoolyst\Services\AuthService;

/**
 * Authentication UI/API entry points: login, logout, register, password flows.
 *
 * Only login/logout are wired up in this phase — this module's dashboard is an
 * internally-provisioned CMS (accounts are created by an admin/seeder via
 * User::create()), not a public sign-up flow, so no register/forgot-password
 * screens are built here. Add them in a later phase if that changes.
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

    public function logout(): never {
        $this->auth->logout();
        Response::redirect(url('/login'));
    }
}
