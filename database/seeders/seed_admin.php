<?php
declare(strict_types=1);

/**
 * One-off CLI seeder: creates a default admin account for local testing.
 * Usage: php database/seeders/seed_admin.php
 */
require dirname(__DIR__, 2) . '/bootstrap/app.php';

use Skoolyst\Models\User;

$email = 'admin@skoolyst.test';

if (User::findByEmail($email)) {
    echo "Admin user already exists ({$email}).\n";
    exit;
}

User::create('Skoolyst Admin', $email, 'change-me-now', 'admin');
echo "Created admin user: {$email} / change-me-now — change this password immediately.\n";
