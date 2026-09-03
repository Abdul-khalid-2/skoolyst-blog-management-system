<?php
return [
    'up' => "CREATE TABLE IF NOT EXISTS blog_post_tags (
        post_id INT UNSIGNED NOT NULL,
        tag_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (post_id, tag_id),
        CONSTRAINT fk_blog_post_tags_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
        CONSTRAINT fk_blog_post_tags_tag FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'down' => 'DROP TABLE IF EXISTS blog_post_tags',
];
