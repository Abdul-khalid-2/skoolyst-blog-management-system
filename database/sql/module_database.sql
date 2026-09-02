-- Module database schema goes here. Keep each module database independent.

-- Phase 4 (Authentication & Security): minimal users table this module needs to
-- authenticate dashboard users. Column names/shape are kept compatible with
-- other Skoolyst modules per app/Models/User.php's convention, but this table
-- lives in THIS module's own database (see ARCHITECTURE.md — module databases
-- are independent) so the module can be stood up and tested on its own.
-- Blog-specific tables (posts, categories, tags, comments, media, etc.) are
-- added in Phase 5 (Database & Models).
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'author') NOT NULL DEFAULT 'author',
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
