<?php
declare(strict_types=1);

namespace Skoolyst\Models;

use Skoolyst\Core\Model;

class Comment extends Model {
    protected string $table = 'blog_comments';
    protected array $fillable = ['post_id', 'author_name', 'author_email', 'body', 'status', 'created_at'];

    public function create(array $data): int {
        $data['status'] ??= 'pending';
        $data['created_at'] ??= date('Y-m-d H:i:s');
        return parent::create($data);
    }

    public function approvedForPost(int $postId): array {
        return $this->where(['post_id' => $postId, 'status' => 'approved'], 'created_at ASC');
    }

    public function pending(): array {
        return $this->where(['status' => 'pending'], 'created_at DESC');
    }

    /**
     * Pending comments with their parent post's title/slug/author joined in.
     * $authorId, when given, restricts results to comments on that author's own posts — the
     * actual authorization boundary for the author role, not just a UI filter, and always wins
     * over $filters['author_id']. $filters (optional): 'author_id' (admin/editor only — narrows
     * to one author's posts) and 'search' (matches comment body or commenter name).
     */
    public function pendingWithPost(?int $authorId = null, array $filters = []): array {
        $where = ["c.status = 'pending'"];
        $params = [];

        $scopeAuthorId = $authorId ?? (!empty($filters['author_id']) ? (int) $filters['author_id'] : null);
        if ($scopeAuthorId !== null) {
            $where[] = 'p.author_id = :author_id';
            $params['author_id'] = $scopeAuthorId;
        }
        if (!empty($filters['search'])) {
            $where[] = '(c.body LIKE :search OR c.author_name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        return $this->rawQuery(
            "SELECT c.*, p.title AS post_title, p.slug AS post_slug, p.author_id AS post_author_id
             FROM {$this->table} c
             INNER JOIN blog_posts p ON p.id = c.post_id
             WHERE " . implode(' AND ', $where) . '
             ORDER BY c.created_at DESC',
            $params
        );
    }

    /** Same author scoping as pendingWithPost(), but just the count — for the topbar notification badge. */
    public function countPending(?int $authorId = null): int {
        $where = ["c.status = 'pending'"];
        $params = [];
        if ($authorId !== null) {
            $where[] = 'p.author_id = :author_id';
            $params['author_id'] = $authorId;
        }
        return (int) $this->rawScalar(
            "SELECT COUNT(*) FROM {$this->table} c
             INNER JOIN blog_posts p ON p.id = c.post_id
             WHERE " . implode(' AND ', $where),
            $params
        );
    }

    /** A single comment plus its parent post's author_id, for the approve/reject ownership check. */
    public function findWithPostAuthor(int $id): ?array {
        $rows = $this->rawQuery(
            "SELECT c.*, p.author_id AS post_author_id
             FROM {$this->table} c
             INNER JOIN blog_posts p ON p.id = c.post_id
             WHERE c.id = :id LIMIT 1",
            ['id' => $id]
        );
        return $rows[0] ?? null;
    }
}
