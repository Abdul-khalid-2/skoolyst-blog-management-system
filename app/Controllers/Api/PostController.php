<?php
declare(strict_types=1);

namespace Skoolyst\Controllers\Api;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Services\PostService;

class PostController {
    public function __construct(private PostService $posts = new PostService()) {}

    public function index(): never {
        $page = max(1, (int) Request::query('page', 1));
        $search = Request::query('q');
        $sort = (string) Request::query('sort', 'newest');
        $result = $this->posts->publicList($page, $search ?: null, null, $sort);

        Response::json([
            'data' => array_map([$this->posts, 'toApiArray'], $result['data']),
            'meta' => [
                'page' => $result['page'],
                'per_page' => $result['perPage'],
                'total' => $result['total'],
                'total_pages' => $result['totalPages'],
            ],
        ]);
    }

    public function show(string $slug): never {
        $post = $this->posts->bySlug($slug, trackView: false);
        if (!$post) {
            Response::json(['error' => 'Post not found'], 404);
        }

        Response::json(['data' => [
            ...$this->posts->toApiArray($post),
            'body' => $post['body'],
            'tags' => array_column($this->posts->tagsFor((int) $post['id']), 'name'),
        ]]);
    }
}
