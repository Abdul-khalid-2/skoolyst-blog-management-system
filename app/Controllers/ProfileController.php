<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Core\View;
use Skoolyst\Services\ProfileService;

class ProfileController {
    public function __construct(private ProfileService $profile = new ProfileService()) {}

    public function edit(): void {
        View::render('admin/profile', [
            'title' => 'Edit Profile',
            'activeNav' => 'profile',
        ], 'admin');
    }

    public function update(): mixed {
        $errors = Validator::make(Request::all(), ['name' => 'required|max:120']);

        $newPassword = (string) Request::input('new_password', '');
        if ($newPassword !== '') {
            if (mb_strlen($newPassword) < 8) {
                $errors['new_password'][] = 'New password must be at least 8 characters.';
            }
            if ($newPassword !== (string) Request::input('new_password_confirmation', '')) {
                $errors['new_password_confirmation'][] = 'Passwords do not match.';
            }
            if ((string) Request::input('current_password', '') === '') {
                $errors['current_password'][] = 'Current password is required to set a new one.';
            }
        }

        if ($errors) {
            flash('error', 'Please fix the errors below.');
            return View::render('admin/profile', [
                'title' => 'Edit Profile', 'activeNav' => 'profile', 'errors' => $errors,
            ], 'admin');
        }

        $failureMessage = $this->profile->update(
            (int) auth_user()['id'],
            (string) Request::input('name'),
            (string) Request::input('current_password', '') ?: null,
            $newPassword ?: null,
        );

        flash($failureMessage === null ? 'success' : 'error', $failureMessage ?? 'Profile updated.');
        return Response::redirect(url('/dashboard/profile'));
    }
}
