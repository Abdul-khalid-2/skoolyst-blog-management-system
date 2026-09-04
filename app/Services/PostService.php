<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Models\AuditLog;
use Skoolyst\Models\Post;
use Skoolyst\Models\Tag;

class PostService {
    public function __construct(
        private Post $posts = new Post(),
        private Tag $tags = new Tag(),
        private AuditLog $audit = new AuditLog(),
    ) {}

    public function forHomepage(int $featuredCount = 3, int $latestCount = 3): array {
        $featured = $this->posts->where(['status' => 'published'], 'views DESC', $featuredCount);
        $excludeIds = array_column($featured, 'id');
        $latest = array_filter(
            $this->posts->where(['status' => 'published'], 'created_at DESC', $latestCount + count($excludeIds)),
            fn ($post) => !in_array($post['id'], $excludeIds, true)
        );
        return ['featured' => $featured, 'latest' => array_slice(array_values($latest), 0, $latestCount)];
    }

    public function publicList(int $page = 1, ?string $search = null, ?int $categoryId = null, string $sort = 'newest'): array {
        $conditions = ['status' => 'published'];
        if ($categoryId) $conditions['category_id'] = $categoryId;

        if ($search) {
            // Search needs LIKE, which the generic Model::where() doesn't support — query directly.
            $perPage = 12;
            $params = ['status' => 'published', 'q' => '%' . $search . '%'];
            $catClause = '';
            if ($categoryId) { $catClause = ' AND category_id = :cat'; $params['cat'] = $categoryId; }
            $where = "status = :status{$catClause} AND (title LIKE :q OR excerpt LIKE :q) AND deleted_at IS NULL";
            $order = $sort === 'oldest' ? 'created_at ASC' : 'created_at DESC';

            $total = (int) $this->posts->rawScalar("SELECT COUNT(*) FROM blog_posts WHERE {$where}", $params);
            $offset = (max(1, $page) - 1) * $perPage;
            $rows = $this->posts->rawQuery(
                "SELECT * FROM blog_posts WHERE {$where} ORDER BY {$order} LIMIT {$perPage} OFFSET {$offset}",
                $params
            );

            return [
                'data' => $rows,
                'total' => $total,
                'page' => max(1, $page),
                'perPage' => $perPage,
                'totalPages' => (int) max(1, ceil($total / $perPage)),
            ];
        }

        return $this->posts->paginate($page, 12, $conditions, $sort === 'oldest' ? 'created_at ASC' : 'created_at DESC');
    }

    /**
     * Published posts to show alongside $post: same category first (newest first),
     * topped up with the site's latest published posts if the category doesn't have
     * enough on its own. Never includes $post itself or duplicates.
     */
    public function relatedTo(array $post, int $limit = 3): array {
        $excludeId = (int) $post['id'];
        $related = [];

        if (!empty($post['category_id'])) {
            $related = $this->posts->rawQuery(
                "SELECT * FROM blog_posts WHERE status = 'published' AND deleted_at IS NULL
                 AND category_id = :cat AND id != :id
                 ORDER BY published_date DESC, created_at DESC LIMIT " . $limit,
                ['cat' => $post['category_id'], 'id' => $excludeId]
            );
        }

        if (count($related) < $limit) {
            $excludeIds = array_merge([$excludeId], array_column($related, 'id'));
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $latest = $this->posts->rawQuery(
                "SELECT * FROM blog_posts WHERE status = 'published' AND deleted_at IS NULL
                 AND id NOT IN ({$placeholders})
                 ORDER BY published_date DESC, created_at DESC LIMIT " . ($limit - count($related)),
                $excludeIds
            );
            $related = array_merge($related, $latest);
        }

        return $related;
    }

    public function bySlug(string $slug, bool $trackView = true): ?array {
        $post = $this->posts->findBySlug($slug);
        if ($post && $trackView) {
            $this->posts->incrementViews((int) $post['id']);
        }
        return $post;
    }

    /** $authorId scopes the list to one author's own posts — see Post::paginateForDashboard(). */
    public function dashboardList(int $page = 1, ?int $authorId = null, array $filters = []): array {
        return $this->posts->paginateForDashboard($page, 15, $authorId, $filters);
    }

    /** True if $userRole may manage $post — 'author' accounts may only manage their own posts; editor/admin manage all. */
    public function canManage(array $post, int $userId, string $userRole): bool {
        return $userRole !== 'author' || (int) $post['author_id'] === $userId;
    }

    public function tagsFor(int $postId): array {
        return $this->tags->forPost($postId);
    }

    public function create(array $data, int $authorId, array $tagIds = []): int {
        $data['slug'] = $this->slugify(($data['slug'] ?? '') !== '' ? $data['slug'] : $data['title']);
        $data['author_id'] = $authorId;
        $data['body'] = sanitize_html($data['body'] ?? '');
        $data['read_time_minutes'] = max(1, (int) ceil(str_word_count(strip_tags($data['body'])) / 200));
        if (($data['status'] ?? 'draft') === 'published') {
            $data['published_date'] ??= date('Y-m-d');
        }

        $id = $this->posts->create($data);
        $this->syncTags($id, $tagIds);
        $this->audit->record($authorId, 'post.created', 'post', $id, ['title' => $data['title']]);
        return $id;
    }

    public function update(int $id, array $data, int $userId, array $tagIds = []): bool {
        if (!empty($data['slug'])) {
            $data['slug'] = $this->slugify($data['slug'], $id);
        } elseif (!empty($data['title'])) {
            $data['slug'] = $this->slugify($data['title'], $id);
        }
        if (isset($data['body'])) {
            $data['body'] = sanitize_html($data['body']);
            $data['read_time_minutes'] = max(1, (int) ceil(str_word_count(strip_tags($data['body'])) / 200));
        }
        if (($data['status'] ?? null) === 'published') {
            $existing = $this->posts->find($id);
            $data['published_date'] ??= $existing['published_date'] ?? date('Y-m-d');
        }

        $ok = $this->posts->update($id, $data);
        $this->syncTags($id, $tagIds);
        $this->audit->record($userId, 'post.updated', 'post', $id);
        return $ok;
    }

    public function delete(int $id, int $userId): bool {
        $ok = $this->posts->delete($id);
        $this->audit->record($userId, 'post.deleted', 'post', $id);
        return $ok;
    }

    public function toApiArray(array $post): array {
        return [
            'id' => (int) $post['id'],
            'title' => $post['title'],
            'slug' => $post['slug'],
            'excerpt' => $post['excerpt'],
            'cover_image' => $post['cover_image'],
            'category_id' => $post['category_id'] !== null ? (int) $post['category_id'] : null,
            'published_date' => $post['published_date'],
            'read_time_minutes' => (int) $post['read_time_minutes'],
            'views' => (int) $post['views'],
        ];
    }

    private function syncTags(int $postId, array $tagIds): void {
        if (!$tagIds) return;
        $current = array_column($this->tags->forPost($postId), 'id');
        foreach (array_diff($tagIds, $current) as $tagId) $this->tags->attachToPost((int) $tagId, $postId);
        foreach (array_diff($current, $tagIds) as $tagId) $this->tags->detachFromPost((int) $tagId, $postId);
    }

    /**
     * Create any tags in $names that don't already exist (matched by slug),
     * then attach the full resulting set ($tagIds + the new ones) to the post
     * and detach anything no longer selected. Used by the post editor's tag
     * picker, which offers existing tags as checkboxes plus a free-text field
     * for new ones.
     */
    public function syncTagsFromEditor(int $postId, array $tagIds, string $newTagNames): void {
        $tagIds = array_map('intval', $tagIds);
        foreach (array_filter(array_map('trim', explode(',', $newTagNames))) as $name) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
            if ($slug === '') continue;
            $existing = $this->tags->findBySlug($slug);
            $tagIds[] = $existing ? (int) $existing['id'] : $this->tags->create(['name' => $name, 'slug' => $slug]);
        }

        $current = array_column($this->tags->forPost($postId), 'id');
        foreach (array_diff($tagIds, $current) as $tagId) $this->tags->attachToPost((int) $tagId, $postId);
        foreach (array_diff($current, $tagIds) as $tagId) $this->tags->detachFromPost((int) $tagId, $postId);
    }

    /** $excludeId lets an existing post keep its own slug unchanged instead of colliding with itself on every save. */
    public function slugify(string $title, ?int $excludeId = null): string {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $title), '-')) ?: 'post';
        $slug = $base;
        $suffix = 1;
        while (($existing = $this->posts->findBySlug($slug)) && (int) $existing['id'] !== $excludeId) {
            $slug = $base . '-' . (++$suffix);
        }
        return $slug;
    }
}
