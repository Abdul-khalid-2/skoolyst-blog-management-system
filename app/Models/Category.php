<?php
declare(strict_types=1);

namespace Skoolyst\Models;

use Skoolyst\Core\Model;

class Category extends Model {
    protected string $table = 'blog_categories';
    protected array $fillable = ['name', 'slug', 'description', 'color', 'created_at', 'updated_at'];

    public function findBySlug(string $slug): ?array {
        $rows = $this->where(['slug' => $slug], null, 1);
        return $rows[0] ?? null;
    }

    public function create(array $data): int {
        $data['created_at'] ??= date('Y-m-d H:i:s');
        $data['updated_at'] ??= date('Y-m-d H:i:s');
        return parent::create($data);
    }

    public function update(int|string $id, array $data): bool {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }
}
