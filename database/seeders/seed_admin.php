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

User::create('Skoolyst Admin', $email, 'nmdp7788', 'admin');
echo "Created admin user: {$email} / nmdp7788 — change this password immediately.\n";
