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
}
