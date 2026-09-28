<?php
// Tracks actual cumulative reading time (in seconds, summed across all visitors'
// 5-second engagement pings), separate from read_time_minutes which is a static
// author-set estimate based on content length.
return [
    'up' => "ALTER TABLE blog_posts ADD COLUMN read_seconds INT UNSIGNED NOT NULL DEFAULT 0 AFTER views",
    'down' => "ALTER TABLE blog_posts DROP COLUMN read_seconds",
];
