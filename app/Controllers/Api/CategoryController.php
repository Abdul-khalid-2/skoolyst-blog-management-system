<?php
declare(strict_types=1);

namespace Skoolyst\Controllers\Api;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Services\CategoryService;
use Skoolyst\Services\PostService;

class CategoryController {
    public function __construct(
        private CategoryService $categories = new CategoryService(),
        private PostService $posts = new PostService(),
    ) {}

    public function index(): never {
        Response::json(['data' => $this->categories->all()]);
    }

    public function show(string $slug): never {
        $category = $this->categories->bySlug($slug);
        if (!$category) {
            Response::json(['error' => 'Category not found'], 404);
        }

        $page = max(1, (int) Request::query('page', 1));
        $result = $this->posts->publicList($page, null, (int) $category['id']);

        Response::json([
            'data' => ['category' => $category, 'posts' => array_map([$this->posts, 'toApiArray'], $result['data'])],
            'meta' => ['page' => $result['page'], 'total' => $result['total'], 'total_pages' => $result['totalPages']],
        ]);
    }
}
