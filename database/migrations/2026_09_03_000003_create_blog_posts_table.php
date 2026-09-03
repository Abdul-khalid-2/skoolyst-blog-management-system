<?php
return [
    'up' => "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(220) NOT NULL,
        slug VARCHAR(220) NOT NULL UNIQUE,
        excerpt TEXT NULL,
        body LONGTEXT NOT NULL,
        cover_image VARCHAR(255) NULL,
        category_id INT UNSIGNED NULL,
        author_id INT UNSIGNED NULL,
        status ENUM('draft','published') NOT NULL DEFAULT 'draft',
        published_date DATE NULL,
        views INT UNSIGNED NOT NULL DEFAULT 0,
        read_time_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 1,
        seo_title VARCHAR(220) NULL,
        seo_description VARCHAR(320) NULL,
        deleted_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        INDEX idx_blog_posts_status (status, deleted_at),
        INDEX idx_blog_posts_category (category_id, deleted_at),
        INDEX idx_blog_posts_published_date (published_date),
        CONSTRAINT fk_blog_posts_category FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
        CONSTRAINT fk_blog_posts_author FOREIGN KEY (author_id) REFERENCES blog_users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'down' => 'DROP TABLE IF EXISTS blog_posts',
];
