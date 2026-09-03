<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Models\AuditLog;
use Skoolyst\Models\Comment;

class CommentService {
    public function __construct(
        private Comment $comments = new Comment(),
        private AuditLog $audit = new AuditLog(),
    ) {}

    /** Public comment submissions are always saved as pending — never auto-approved. */
    public function submit(int $postId, string $name, string $email, string $body): int {
        return $this->comments->create([
            'post_id' => $postId,
            'author_name' => $name,
            'author_email' => $email,
            'body' => $body,
            'status' => 'pending',
        ]);
    }

    public function approvedForPost(int $postId): array {
        return $this->comments->approvedForPost($postId);
    }

    public function pending(): array {
        return $this->comments->pending();
    }

    public function approve(int $id, int $userId): bool {
        $ok = $this->comments->update($id, ['status' => 'approved']);
        $this->audit->record($userId, 'comment.approved', 'comment', $id);
        return $ok;
    }

    public function reject(int $id, int $userId): bool {
        $ok = $this->comments->update($id, ['status' => 'rejected']);
        $this->audit->record($userId, 'comment.rejected', 'comment', $id);
        return $ok;
    }
}
