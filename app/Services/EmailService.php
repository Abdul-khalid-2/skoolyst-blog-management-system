<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Models\EmailQueue;

/**
 * Client for the shared Skoolyst Email API (POST {base}/email/send on ads.skoolyst.com).
 *
 * Sending is never done inline with the user's action: queue() writes a row to
 * blog_email_queue and returns immediately. Delivery happens either
 *  - right after the response has been sent to the browser (send_inline, default), or
 *  - from the cron worker, bin/send-emails.php, which also retries temporary failures.
 * An email problem therefore never breaks or slows down signup, posting or commenting.
 */
class EmailService {
    /** Seconds to wait before retry N (1-based). Beyond the list, the last value is reused. */
    private const BACKOFF = [60, 300, 900, 3600, 3 * 3600];
    private const MAX_ATTEMPTS = 6;
    /** all_accounts_exhausted clears when the sender limits reset daily, so keep trying for this long regardless of attempts. */
    private const EXHAUSTED_GIVE_UP_HOURS = 48;

    /** Ids queued during this request, flushed once at shutdown. */
    private static array $queuedThisRequest = [];
    private static bool $shutdownRegistered = false;

    private array $config;

    public function __construct(private EmailQueue $queue = new EmailQueue()) {
        $this->config = require dirname(__DIR__, 2) . '/config/mail.php';
    }

    public function isConfigured(): bool {
        return $this->config['enabled'] && $this->config['api_key'] !== '' && $this->config['base_url'] !== '';
    }

    /** Queue a plain-text email. Returns false (and queues nothing) for an invalid address or when mail is not configured. */
    public function queue(string $to, string $subject, string $body, ?string $event = null): bool {
        $to = trim($to);
        if (!$this->isConfigured() || filter_var($to, FILTER_VALIDATE_EMAIL) === false) return false;

        try {
            $id = $this->queue->create([
                'to_email' => $to,
                'subject' => mb_substr(trim(preg_replace('/\s+/', ' ', $subject)), 0, 255),
                'body' => mb_substr($body, 0, 100000),
                'event' => $event,
            ]);
        } catch (\Throwable $e) {
            // e.g. the migration hasn't been run yet — never let email break the user's action.
            $this->log('Could not queue email: ' . $e->getMessage());
            return false;
        }

        if ($this->config['send_inline']) {
            self::$queuedThisRequest[] = $id;
            $this->registerShutdownFlush();
        }
        return true;
    }

    /**
     * Deliver due rows. $onlyIds restricts to specific rows (the post-response flush only
     * sends what this request queued, so a visitor's request never works through a backlog).
     * @return array{sent:int, retry:int, failed:int}
     */
    public function flush(int $limit = 50, array $onlyIds = []): array {
        $stats = ['sent' => 0, 'retry' => 0, 'failed' => 0];
        if (!$this->isConfigured()) return $stats;

        if (!$onlyIds) $this->queue->releaseStale();

        foreach ($this->queue->dueIds($limit, $onlyIds) as $id) {
            if (!$this->queue->claim($id)) continue;
            $row = $this->queue->find($id);
            if (!$row) continue;
            $stats[$this->deliver($row)]++;
        }
        return $stats;
    }

    /** @return 'sent'|'retry'|'failed' */
    private function deliver(array $row): string {
        $id = (int) $row['id'];
        $attempts = (int) $row['attempts'] + 1;
        [$status, $response, $transportError] = $this->post('/email/send', [
            'source_app' => $this->config['source_app'],
            'to' => $row['to_email'],
            'subject' => $row['subject'],
            'body' => $row['body'],
        ]);

        if ($status === 201 && ($response['success'] ?? false)) {
            $this->queue->update($id, [
                'status' => 'sent',
                'attempts' => $attempts,
                'sent_at' => date('Y-m-d H:i:s'),
                'remote_message_id' => isset($response['data']['message_id']) ? (int) $response['data']['message_id'] : null,
                'last_error' => null,
            ]);
            return 'sent';
        }

        $code = (string) ($response['error']['code'] ?? '');
        $error = $transportError ?? trim("HTTP {$status} {$code}: " . (string) ($response['error']['message'] ?? ''));

        // 422: the request itself is bad (address/subject/body) — retrying can't help.
        $permanent = $status === 422;
        $exhausted = $status === 503 && $code === 'all_accounts_exhausted';
        if ($exhausted) {
            $permanent = strtotime((string) $row['created_at']) < time() - self::EXHAUSTED_GIVE_UP_HOURS * 3600;
        } elseif ($attempts >= self::MAX_ATTEMPTS) {
            $permanent = true;
        }

        if ($permanent) {
            $this->queue->update($id, ['status' => 'failed', 'attempts' => $attempts, 'last_error' => mb_substr($error, 0, 500)]);
            $this->log("Email #{$id} to {$row['to_email']} failed permanently: {$error}");
            return 'failed';
        }

        if ($status === 401 || $status === 403) {
            // Configuration problem (bad key, or SKOOLYST_EMAIL_SOURCE_APP not matching the key's app name) — make it visible.
            $this->log("Email #{$id} rejected — check SKOOLYST_EMAIL_API_KEY / SKOOLYST_EMAIL_SOURCE_APP in .env: {$error}");
        }

        // Rate limited or out of sender capacity: back off harder, since retrying soon will just fail again.
        $delay = match (true) {
            $exhausted => 3600,
            $status === 429 => 120,
            default => self::BACKOFF[min($attempts, count(self::BACKOFF)) - 1],
        };
        $this->queue->update($id, [
            'status' => 'pending',
            'attempts' => $attempts,
            'claimed_at' => null,
            'next_attempt_at' => date('Y-m-d H:i:s', time() + $delay),
            'last_error' => mb_substr($error, 0, 500),
        ]);
        return 'retry';
    }

    /** @return array{0:int, 1:array, 2:?string} [HTTP status, decoded body, transport error or null] */
    private function post(string $path, array $payload): array {
        $ch = curl_init($this->config['base_url'] . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                // Key goes in the header rather than the JSON body so it never lands in request-body logs.
                'Authorization: Bearer ' . $this->config['api_key'],
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            // Covers DNS + TCP + TLS handshake, which to ads.skoolyst.com has been seen to exceed 5s.
            CURLOPT_CONNECTTIMEOUT => 20,
            // A send does SMTP on the far side and can take several seconds.
            CURLOPT_TIMEOUT => 30,
        ]);
        $raw = curl_exec($ch);
        $transportError = $raw === false ? 'Transport error: ' . curl_error($ch) : null;
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        return [$status, is_array($decoded) ? $decoded : [], $transportError];
    }

    /**
     * Send this request's emails after the browser already has its response, so a
     * multi-second SMTP round trip never delays a redirect. Under PHP-FPM that's
     * fastcgi_finish_request(); under mod_php (XAMPP) the connection is closed via
     * Content-Length/Connection headers when possible — and the cron worker picks up
     * anything left over either way.
     */
    private function registerShutdownFlush(): void {
        if (self::$shutdownRegistered) return;
        self::$shutdownRegistered = true;

        register_shutdown_function(function (): void {
            $ids = self::$queuedThisRequest;
            self::$queuedThisRequest = [];
            if (!$ids) return;

            if (PHP_SAPI !== 'cli') {
                if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
                ignore_user_abort(true);
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                } elseif (!headers_sent()) {
                    // Collect every buffered level (outermost content comes first) so Content-Length is exact.
                    $output = '';
                    while (ob_get_level() > 0) $output = (string) ob_get_clean() . $output;
                    header('Connection: close');
                    header('Content-Encoding: none');
                    header('Content-Length: ' . strlen($output));
                    echo $output;
                    flush();
                } else {
                    while (ob_get_level() > 0) ob_end_flush();
                    flush();
                }
            }

            try {
                $this->flush(count($ids), $ids);
            } catch (\Throwable $e) {
                $this->log('Post-response email flush failed: ' . $e->getMessage());
            }
        });
    }

    private function log(string $message): void {
        error_log('[' . date('Y-m-d H:i:s') . "] [email] {$message}\n", 3, dirname(__DIR__, 2) . '/storage/logs/app.log');
    }
}
