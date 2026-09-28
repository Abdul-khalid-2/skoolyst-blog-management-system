<?php
declare(strict_types=1);

/**
 * Delivers queued email (blog_email_queue) through the Skoolyst Email API and
 * retries temporary failures (503 out-of-capacity, 429, timeouts) with backoff.
 *
 * Usage:
 *   php bin/send-emails.php          — send up to 50 due emails
 *   php bin/send-emails.php 200      — send up to 200
 *
 * Suggested cron (every minute):
 *   * * * * * php /path/to/app/bin/send-emails.php >> /path/to/app/storage/logs/email-cron.log 2>&1
 */
require dirname(__DIR__) . '/bootstrap/app.php';

use Skoolyst\Services\EmailService;

$mail = new EmailService();
if (!$mail->isConfigured()) {
    fwrite(STDERR, "Email is not configured — set SKOOLYST_EMAIL_API_KEY in .env (and MAIL_ENABLED=true).\n");
    exit(1);
}

$limit = max(1, (int) ($argv[1] ?? 50));
$stats = $mail->flush($limit);
echo '[' . date('Y-m-d H:i:s') . "] sent={$stats['sent']} retry={$stats['retry']} failed={$stats['failed']}\n";
