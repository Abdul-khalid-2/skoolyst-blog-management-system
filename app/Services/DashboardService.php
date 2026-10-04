<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Models\Comment;
use Skoolyst\Models\Post;

class DashboardService {
    /** Roles whose dashboard only shows their own posts (and comments/views on them); admins see site-wide numbers. */
    private const OWN_DATA_ROLES = ['author', 'editor'];

    public function __construct(
        private Post $posts = new Post(),
        private Comment $comments = new Comment(),
    ) {}

    /** The author_id to scope the dashboard to for $user, or null for a site-wide view (admin). */
    public function scopeFor(array $user): ?int {
        return in_array($user['role'] ?? '', self::OWN_DATA_ROLES, true) ? (int) $user['id'] : null;
    }

    public function stats(?int $authorId = null): array {
        $own = $authorId !== null ? ['author_id' => $authorId] : [];
        $viewsSql = 'SELECT COALESCE(SUM(views), 0) FROM blog_posts WHERE deleted_at IS NULL'
            . ($authorId !== null ? ' AND author_id = :author_id' : '');

        return [
            'published' => $this->posts->count(['status' => 'published'] + $own),
            'draft' => $this->posts->count(['status' => 'draft'] + $own),
            'comments' => $this->comments->countPending($authorId),
            'views' => (int) ($this->posts->rawScalar($viewsSql, $authorId !== null ? ['author_id' => $authorId] : []) ?? 0),
        ];
    }

    public function recentPosts(?int $authorId = null, int $limit = 5): array {
        return $this->posts->paginateForDashboard(1, $limit, $authorId)['data'];
    }
}
