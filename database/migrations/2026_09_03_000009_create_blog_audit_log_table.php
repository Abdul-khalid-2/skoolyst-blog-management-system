<?php
return [
    'up' => "CREATE TABLE IF NOT EXISTS blog_audit_log (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NULL,
        action VARCHAR(120) NOT NULL,
        entity_type VARCHAR(80) NULL,
        entity_id VARCHAR(80) NULL,
        details JSON NULL,
        created_at DATETIME NOT NULL,
        CONSTRAINT fk_blog_audit_user FOREIGN KEY (user_id) REFERENCES blog_users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'down' => 'DROP TABLE IF EXISTS blog_audit_log',
];
