<?php
// Adds the public-facing 'reader' role alongside the existing internally-
// provisioned admin/editor/author roles, for the public signup feature.
// down assumes no 'reader' rows exist yet (rolling back after readers have
// signed up would need those rows reassigned/removed first).
return [
    'up' => "ALTER TABLE blog_users MODIFY COLUMN role ENUM('admin','editor','author','reader') NOT NULL DEFAULT 'author'",
    'down' => "ALTER TABLE blog_users MODIFY COLUMN role ENUM('admin','editor','author') NOT NULL DEFAULT 'author'",
];
