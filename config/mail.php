<?php
// Outbound email goes through the shared Skoolyst Email API hosted on ads.skoolyst.com
// (see EmailService). The key is issued in Admin → Email Accounts → API Clients on that
// host, for the app name below — source_app must match it exactly or the API returns 403.
return [
    'enabled' => filter_var($_ENV['MAIL_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'base_url' => rtrim((string) ($_ENV['SKOOLYST_EMAIL_API_BASE'] ?? ($_ENV['ADS_API_BASE'] ?? 'https://ads.skoolyst.com/api/v1')), '/'),
    'api_key' => (string) ($_ENV['SKOOLYST_EMAIL_API_KEY'] ?? ''),
    'source_app' => (string) ($_ENV['SKOOLYST_EMAIL_SOURCE_APP'] ?? 'skoolyst-blogs'),
    // true: deliver right after the response is sent (no cron needed for the happy path).
    // false: only bin/send-emails.php delivers — set this when a cron job is configured.
    'send_inline' => filter_var($_ENV['MAIL_SEND_INLINE'] ?? true, FILTER_VALIDATE_BOOLEAN),
    // Inbox for admin-facing notices (new signups etc.) in addition to active admin accounts. Optional.
    'admin_address' => (string) ($_ENV['MAIL_ADMIN_ADDRESS'] ?? ''),
];
