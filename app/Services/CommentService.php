<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Models\AuditLog;
use Skoolyst\Models\Comment;
use Skoolyst\Models\Post;

class CommentService {
    public function __construct(
        private Comment $comments = new Comment(),
        private AuditLog $audit = new AuditLog(),
        private NotificationService $notify = new NotificationService(),
    ) {}

    /** Public comment submissions are always saved as pending — never auto-approved. */
    public function submit(int $postId, string $name, string $email, string $body): int {
        $comment = [
            'post_id' => $postId,
            'author_name' => $name,
            'author_email' => $email,
            'body' => $body,
            'status' => 'pending',
        ];
        $id = $this->comments->create($comment);

        if ($post = (new Post())->find($postId)) $this->notify->commentSubmitted($post, $comment + ['id' => $id]);
        return $id;
    }

    public function approvedForPost(int $postId): array {
        return $this->comments->approvedForPost($postId);
    }

    /** $authorId, when given, restricts the list to comments on that author's own posts — see Comment::pendingWithPost(). */
    public function pending(?int $authorId = null, array $filters = []): array {
        return $this->comments->pendingWithPost($authorId, $filters);
    }

    public function findWithPostAuthor(int $id): ?array {
        return $this->comments->findWithPostAuthor($id);
    }

    /** True if $userRole may manage $comment — 'author' accounts may only manage comments on their own posts; editor/admin manage all. Mirrors PostService::canManage. */
    public function canManage(array $comment, int $userId, string $userRole): bool {
        return $userRole !== 'author' || (int) $comment['post_author_id'] === $userId;
    }

    public function approve(int $id, int $userId): bool {
        $comment = $this->comments->find($id);
        $ok = $this->comments->update($id, ['status' => 'approved']);
        $this->audit->record($userId, 'comment.approved', 'comment', $id);

        // Only on the transition into approved, so re-approving never re-sends the email.
        if ($ok && $comment && $comment['status'] !== 'approved' && ($post = (new Post())->find((int) $comment['post_id']))) {
            $this->notify->commentApproved($comment, $post);
        }
        return $ok;
    }

    public function reject(int $id, int $userId): bool {
        $ok = $this->comments->update($id, ['status' => 'rejected']);
        $this->audit->record($userId, 'comment.rejected', 'comment', $id);
        return $ok;
    }
}
