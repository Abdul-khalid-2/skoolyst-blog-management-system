<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Core\Session;
use Skoolyst\Models\User;

class ProfileService {
    /**
     * Update the logged-in user's name, and optionally their password.
     * Returns null on success, or an error message to show on failure.
     */
    public function update(int $userId, string $name, ?string $currentPassword, ?string $newPassword): ?string {
        $user = User::findById($userId);
        if (!$user) return 'Account not found.';

        $data = ['name' => $name];

        if ($newPassword !== null && $newPassword !== '') {
            if ($currentPassword === null || !password_verify($currentPassword, $user['password'])) {
                return 'Current password is incorrect.';
            }
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        User::update($userId, $data);
        $updated = User::findById($userId);
        Session::put('user', User::forSession($updated));

        if (isset($data['password'])) {
            (new NotificationService())->passwordChanged($updated);
        }

        return null;
    }
}
