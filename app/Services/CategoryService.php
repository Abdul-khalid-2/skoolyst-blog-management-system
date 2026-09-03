<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Models\AuditLog;
use Skoolyst\Models\Category;

class CategoryService {
    public function __construct(
        private Category $categories = new Category(),
        private AuditLog $audit = new AuditLog(),
    ) {}

    public function all(): array {
        return $this->categories->all('name ASC');
    }

    public function bySlug(string $slug): ?array {
        return $this->categories->findBySlug($slug);
    }

    public function create(array $data, int $userId): int {
        $data['slug'] = ($data['slug'] ?? '') ?: $this->slugify($data['name']);
        $id = $this->categories->create($data);
        $this->audit->record($userId, 'category.created', 'category', $id, ['name' => $data['name']]);
        return $id;
    }

    public function update(int $id, array $data, int $userId): bool {
        if (!empty($data['name']) && empty($data['slug'])) {
            $data['slug'] = $this->slugify($data['name']);
        }
        $ok = $this->categories->update($id, $data);
        $this->audit->record($userId, 'category.updated', 'category', $id);
        return $ok;
    }

    public function delete(int $id, int $userId): bool {
        $ok = $this->categories->delete($id);
        $this->audit->record($userId, 'category.deleted', 'category', $id);
        return $ok;
    }

    private function slugify(string $name): string {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-')) ?: 'category';
        $slug = $base;
        $suffix = 1;
        while ($this->categories->findBySlug($slug)) {
            $slug = $base . '-' . (++$suffix);
        }
        return $slug;
    }
}
