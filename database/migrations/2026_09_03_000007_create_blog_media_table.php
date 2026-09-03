<?php
return [
    'up' => "CREATE TABLE IF NOT EXISTS blog_media (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(190) NOT NULL,
        url VARCHAR(255) NOT NULL,
        size VARCHAR(20) NULL,
        uploaded_by INT UNSIGNED NULL,
        created_at DATETIME NOT NULL,
        CONSTRAINT fk_blog_media_user FOREIGN KEY (uploaded_by) REFERENCES blog_users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'down' => 'DROP TABLE IF EXISTS blog_media',
];
