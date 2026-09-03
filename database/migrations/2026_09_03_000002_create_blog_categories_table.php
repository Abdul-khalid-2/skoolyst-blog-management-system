<?php
return [
    'up' => "CREATE TABLE IF NOT EXISTS blog_categories (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        slug VARCHAR(150) NOT NULL UNIQUE,
        description TEXT NULL,
        color VARCHAR(7) NOT NULL DEFAULT '#0F4077',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'down' => 'DROP TABLE IF EXISTS blog_categories',
];
