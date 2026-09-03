<?php
declare(strict_types=1);

namespace Skoolyst\Models;

use Skoolyst\Core\Model;

class Tag extends Model {
    protected string $table = 'blog_tags';
    protected array $fillable = ['name', 'slug', 'created_at'];

    public function findBySlug(string $slug): ?array {
        $rows = $this->where(['slug' => $slug], null, 1);
        return $rows[0] ?? null;
    }

    public function create(array $data): int {
        $data['created_at'] ??= date('Y-m-d H:i:s');
        return parent::create($data);
    }

    /** Attach a tag to a post (idempotent — ignores an already-existing pairing). */
    public function attachToPost(int $tagId, int $postId): void {
        $stmt = $this->pdo()->prepare('INSERT IGNORE INTO blog_post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)');
        $stmt->execute(['post_id' => $postId, 'tag_id' => $tagId]);
    }

    public function detachFromPost(int $tagId, int $postId): void {
        $stmt = $this->pdo()->prepare('DELETE FROM blog_post_tags WHERE post_id = :post_id AND tag_id = :tag_id');
        $stmt->execute(['post_id' => $postId, 'tag_id' => $tagId]);
    }

    /** Tags attached to a given post. */
    public function forPost(int $postId): array {
        $stmt = $this->pdo()->prepare(
            'SELECT t.* FROM blog_tags t
             INNER JOIN blog_post_tags pt ON pt.tag_id = t.id
             WHERE pt.post_id = :post_id
             ORDER BY t.name'
        );
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
