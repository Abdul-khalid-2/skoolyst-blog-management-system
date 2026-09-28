<?php
// Outbox for transactional email sent through the shared Skoolyst Email API
// (ads.skoolyst.com). Rows are written during the request and delivered either
// right after the response (EmailService::flushQueued) or by bin/send-emails.php,
// which also retries anything that failed with a temporary error (e.g. a 503
// when every sender account has hit its daily limit).
return [
    'up' => "CREATE TABLE IF NOT EXISTS blog_email_queue (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        to_email VARCHAR(191) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body MEDIUMTEXT NOT NULL,
        event VARCHAR(80) NULL,
        status ENUM('pending','sending','sent','failed') NOT NULL DEFAULT 'pending',
        attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        last_error VARCHAR(500) NULL,
        remote_message_id INT UNSIGNED NULL,
        next_attempt_at DATETIME NOT NULL,
        claimed_at DATETIME NULL,
        sent_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        INDEX idx_blog_email_queue_due (status, next_attempt_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'down' => 'DROP TABLE IF EXISTS blog_email_queue',
];
