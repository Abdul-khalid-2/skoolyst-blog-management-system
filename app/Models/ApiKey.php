<?php
declare(strict_types=1);

namespace Skoolyst\Models;

use Skoolyst\Core\Model;

/**
 * API keys for the JSON API (Phase 8). Only the hash is ever stored —
 * the raw key is shown to the user once, at creation time, by the Service.
 */
class ApiKey extends Model {
    protected string $table = 'blog_api_keys';
    protected array $fillable = ['name', 'key_hash', 'user_id'];

    public function findActiveByHash(string $hash): ?array {
        $stmt = $this->pdo()->prepare("SELECT * FROM {$this->table} WHERE key_hash = :hash AND revoked_at IS NULL LIMIT 1");
        $stmt->execute(['hash' => $hash]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function touchLastUsed(int $id): void {
        $this->pdo()->prepare("UPDATE {$this->table} SET last_used_at = NOW() WHERE id = :id")->execute(['id' => $id]);
    }

    public function revoke(int $id): void {
        $this->pdo()->prepare("UPDATE {$this->table} SET revoked_at = NOW() WHERE id = :id")->execute(['id' => $id]);
    }
}
