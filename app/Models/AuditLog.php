<?php
declare(strict_types=1);

namespace Skoolyst\Models;

use Skoolyst\Core\Model;

/**
 * Write-mostly audit trail for admin actions. Consumed starting in Phase 6,
 * where Services record the actions worth auditing (post publish/delete,
 * category changes, comment moderation, etc.).
 */
class AuditLog extends Model {
    protected string $table = 'blog_audit_log';
    protected array $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'details', 'created_at'];

    public function record(?int $userId, string $action, ?string $entityType = null, string|int|null $entityId = null, array $details = []): int {
        return $this->create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId !== null ? (string) $entityId : null,
            'details' => $details ? json_encode($details) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
