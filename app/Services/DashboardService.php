<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Models\Comment;
use Skoolyst\Models\Post;

class DashboardService {
    public function __construct(
        private Post $posts = new Post(),
        private Comment $comments = new Comment(),
    ) {}

    public function stats(): array {
        return [
            'published' => $this->posts->count(['status' => 'published']),
            'draft' => $this->posts->count(['status' => 'draft']),
            'comments' => $this->comments->count(['status' => 'pending']),
            'views' => (int) ($this->posts->rawScalar('SELECT COALESCE(SUM(views), 0) FROM blog_posts WHERE deleted_at IS NULL') ?? 0),
        ];
    }

    public function recentPosts(int $limit = 5): array {
        return $this->posts->paginateForDashboard(1, $limit)['data'];
    }
}
