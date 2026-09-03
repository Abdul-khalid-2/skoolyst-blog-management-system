<?php
// blog_users — author/admin accounts. Phase 5 correction: renamed from the
// Phase 4 draft's plain `users` to match the blog_ prefix convention.
return [
    'up' => "CREATE TABLE IF NOT EXISTS blog_users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        avatar VARCHAR(255) NULL,
        bio TEXT NULL,
        role ENUM('admin','editor','author') NOT NULL DEFAULT 'author',
        active TINYINT(1) NOT NULL DEFAULT 1,
        last_login_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'down' => 'DROP TABLE IF EXISTS blog_users',
];
