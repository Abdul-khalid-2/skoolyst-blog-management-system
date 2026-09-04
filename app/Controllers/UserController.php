<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Core\View;
use Skoolyst\Models\User;

/** Admin-only account management (roles, active status, removal) — see AdminMiddleware. */
class UserController {
    public function index(): void {
        View::render('admin/users/index', [
            'title' => 'Users',
            'activeNav' => 'users',
            'users' => User::all(),
        ], 'admin');
    }

    public function update(int $id): mixed {
        if ($id === (int) auth_user()['id']) {
            flash('error', 'Use Edit Profile to change your own account.');
            return Response::redirect(url('/dashboard/users'));
        }

        if (!User::findById($id)) {
            flash('error', 'User not found.');
            return Response::redirect(url('/dashboard/users'));
        }

        $errors = Validator::make(Request::all(), ['role' => 'required|in:admin,editor,author,reader']);
        if ($errors) {
            flash('error', 'Please choose a valid role.');
            return Response::redirect(url('/dashboard/users'));
        }

        User::update($id, [
            'role' => Request::input('role'),
            'active' => Request::input('active') ? 1 : 0,
        ]);

        flash('success', 'User updated.');
        return Response::redirect(url('/dashboard/users'));
    }

    public function destroy(int $id): mixed {
        if ($id === (int) auth_user()['id']) {
            flash('error', 'You can\'t delete your own account.');
            return Response::redirect(url('/dashboard/users'));
        }

        User::delete($id);
        flash('success', 'User deleted.');
        return Response::redirect(url('/dashboard/users'));
    }
}
