<?php
declare(strict_types=1);

namespace Skoolyst\Models;

use Skoolyst\Core\Model;

class Media extends Model {
    protected string $table = 'blog_media';
    protected array $fillable = ['name', 'url', 'size', 'uploaded_by', 'created_at'];

    public function create(array $data): int {
        $data['created_at'] ??= date('Y-m-d H:i:s');
        return parent::create($data);
    }

    public function recent(int $limit = 50): array {
        return $this->where([], 'created_at DESC', $limit);
    }
}
