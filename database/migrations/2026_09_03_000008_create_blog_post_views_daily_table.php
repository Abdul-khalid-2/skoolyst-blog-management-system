<?php
return [
    'up' => "CREATE TABLE IF NOT EXISTS blog_post_views_daily (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id INT UNSIGNED NOT NULL,
        view_date DATE NOT NULL,
        view_count INT UNSIGNED NOT NULL DEFAULT 1,
        UNIQUE KEY uniq_blog_views_post_date (post_id, view_date),
        CONSTRAINT fk_blog_views_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'down' => 'DROP TABLE IF EXISTS blog_post_views_daily',
];
