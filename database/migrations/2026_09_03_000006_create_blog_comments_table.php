<?php
return [
    'up' => "CREATE TABLE IF NOT EXISTS blog_comments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id INT UNSIGNED NOT NULL,
        author_name VARCHAR(120) NOT NULL,
        author_email VARCHAR(190) NOT NULL,
        body TEXT NOT NULL,
        status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL,
        INDEX idx_blog_comments_post (post_id),
        INDEX idx_blog_comments_status (status),
        CONSTRAINT fk_blog_comments_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'down' => 'DROP TABLE IF EXISTS blog_comments',
];
