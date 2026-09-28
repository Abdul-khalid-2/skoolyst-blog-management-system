<?php
declare(strict_types=1);

namespace Skoolyst\Models;

use Skoolyst\Core\Model;

/** Outbox rows for EmailService — see the blog_email_queue migration. */
class EmailQueue extends Model {
    protected string $table = 'blog_email_queue';
    protected array $fillable = [
        'to_email', 'subject', 'body', 'event', 'status', 'attempts', 'last_error',
        'remote_message_id', 'next_attempt_at', 'claimed_at', 'sent_at', 'created_at',
    ];

    public function create(array $data): int {
        $now = date('Y-m-d H:i:s');
        $data['status'] ??= 'pending';
        $data['next_attempt_at'] ??= $now;
        $data['created_at'] ??= $now;
        return parent::create($data);
    }

    /** Ids of pending rows that are due, oldest first. $onlyIds restricts to those rows (used for the post-response flush). */
    public function dueIds(int $limit, array $onlyIds = []): array {
        $sql = "SELECT id FROM {$this->table} WHERE status = 'pending' AND next_attempt_at <= NOW()";
        $params = [];
        if ($onlyIds) {
            $sql .= ' AND id IN (' . implode(',', array_fill(0, count($onlyIds), '?')) . ')';
            $params = array_values(array_map('intval', $onlyIds));
        }
        $sql .= ' ORDER BY id ASC LIMIT ' . max(1, $limit);
        return array_map('intval', array_column($this->rawQuery($sql, $params), 'id'));
    }

    /**
     * Atomically move a row from pending to sending. Returns false if another
     * process (the cron worker vs. a post-response flush) already claimed it,
     * so the same email is never sent twice.
     */
    public function claim(int $id): bool {
        $stmt = $this->pdo()->prepare(
            "UPDATE {$this->table} SET status = 'sending', claimed_at = NOW() WHERE id = :id AND status = 'pending'"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    /** Rows stuck in 'sending' (the process died mid-send) go back to pending so the worker retries them. */
    public function releaseStale(int $olderThanMinutes = 10): int {
        $stmt = $this->pdo()->prepare(
            "UPDATE {$this->table} SET status = 'pending', claimed_at = NULL
             WHERE status = 'sending' AND claimed_at < (NOW() - INTERVAL :m MINUTE)"
        );
        $stmt->execute(['m' => $olderThanMinutes]);
        return $stmt->rowCount();
    }
}
