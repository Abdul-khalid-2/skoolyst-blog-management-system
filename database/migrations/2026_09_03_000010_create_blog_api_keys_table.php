<?php
// blog_api_keys — for Phase 8 (API): hashed keys, never store the raw key.
return [
    'up' => "CREATE TABLE IF NOT EXISTS blog_api_keys (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        key_hash VARCHAR(255) NOT NULL UNIQUE,
        user_id INT UNSIGNED NULL,
        last_used_at DATETIME NULL,
        revoked_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        CONSTRAINT fk_blog_api_keys_user FOREIGN KEY (user_id) REFERENCES blog_users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'down' => 'DROP TABLE IF EXISTS blog_api_keys',
];
