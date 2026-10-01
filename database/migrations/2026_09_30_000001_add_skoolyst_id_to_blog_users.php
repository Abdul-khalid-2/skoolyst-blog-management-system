<?php
// Links a local account to its central "Login with Skoolyst" identity (skoolyst.com user id).
// Re-logins match on this, never on email alone — a user's email can change on skoolyst.com.
return [
    'up' => 'ALTER TABLE blog_users ADD COLUMN skoolyst_id INT UNSIGNED NULL UNIQUE AFTER id',
    'down' => 'ALTER TABLE blog_users DROP COLUMN skoolyst_id',
];
