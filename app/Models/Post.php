<?php
declare(strict_types=1);

namespace Skoolyst\Models;

use PDO;
use Skoolyst\Core\Model;

class Post extends Model {
    protected string $table = 'blog_posts';
    protected array $fillable = [
        'title', 'slug', 'excerpt', 'body', 'cover_image', 'category_id', 'author_id',
        'status', 'published_date', 'read_time_minutes', 'seo_title', 'seo_description', 'created_at', 'updated_at',
    ];

    public function create(array $data): int {
        $data['created_at'] ??= date('Y-m-d H:i:s');
        $data['updated_at'] ??= date('Y-m-d H:i:s');
        return parent::create($data);
    }

    public function update(int|string $id, array $data): bool {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }

    /** Excludes soft-deleted posts, matching blog_posts.deleted_at. */
    public function find(int|string $id): ?array {
        $stmt = $this->pdo()->prepare("SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->pdo()->prepare("SELECT * FROM {$this->table} WHERE slug = :slug AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Excludes soft-deleted posts, matching blog_posts.deleted_at. */
    public function count(array $conditions = []): int {
        [$clause, $params] = $this->buildWhere($conditions);
        $where = 'deleted_at IS NULL' . ($clause ? " AND {$clause}" : '');
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** Soft delete — sets deleted_at instead of removing the row. */
    public function delete(int|string $id): bool {
        $stmt = $this->pdo()->prepare("UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function paginatePublished(int $page = 1, int $perPage = 10, ?int $categoryId = null): array {
        return $this->paginateCustom(
            ['status' => 'published'] + ($categoryId ? ['category_id' => $categoryId] : []),
            $page,
            $perPage
        );
    }

    public function paginateForDashboard(int $page = 1, int $perPage = 15): array {
        return $this->paginateCustom([], $page, $perPage);
    }

    /** Like Model::paginate(), but always excludes soft-deleted rows. */
    private function paginateCustom(array $conditions, int $page, int $perPage): array {
        [$clause, $params] = $this->buildWhere($conditions);
        $where = 'WHERE deleted_at IS NULL' . ($clause ? " AND {$clause}" : '');

        $countStmt = $this->pdo()->prepare("SELECT COUNT(*) FROM {$this->table} {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->pdo()->prepare("SELECT * FROM {$this->table} {$where} ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) $stmt->bindValue($key, $value);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (max(1, $page) - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => max(1, $page),
            'perPage' => $perPage,
            'totalPages' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public function incrementViews(int $id): void {
        $this->pdo()->prepare("UPDATE {$this->table} SET views = views + 1 WHERE id = :id")->execute(['id' => $id]);
    }
}
