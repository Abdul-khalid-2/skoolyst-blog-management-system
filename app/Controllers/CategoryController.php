<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Core\View;
use Skoolyst\Services\CategoryService;
use Skoolyst\Services\PostService;

class CategoryController {
    public function __construct(
        private CategoryService $categories = new CategoryService(),
        private PostService $posts = new PostService(),
    ) {}

    public function show(string $slug): mixed {
        $category = $this->categories->bySlug($slug);
        if (!$category) {
            http_response_code(404);
            return View::render('errors/404', [], 'frontend');
        }

        $page = max(1, (int) Request::query('page', 1));
        $result = $this->posts->publicList($page, null, (int) $category['id']);

        $canonical = url('/category/' . $category['slug']);
        $description = $category['description'] ?: ('Browse articles about ' . $category['name'] . ' — product news, teaching resources and stories from Skoolyst.');

        $jsonLdItems = array_map(fn ($post, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'item' => ['@type' => 'BlogPosting', 'headline' => $post['title'], 'url' => url('/post/' . $post['slug'])],
        ], $result['data'], array_keys($result['data']));

        View::render('frontend/category', [
            'title' => $category['name'] . ' — Skoolyst Blog',
            'description' => $description,
            'canonical' => $canonical,
            'ogImage' => url('assets/images/skoolyst-blog.png'),
            'activeNav' => 'blog',
            'category' => $category,
            'posts' => $result['data'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => [
                            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => url('/blog')],
                            ['@type' => 'ListItem', 'position' => 3, 'name' => $category['name'], 'item' => $canonical],
                        ],
                    ],
                    [
                        '@type' => 'CollectionPage',
                        '@id' => $canonical,
                        'name' => $category['name'] . ' — Skoolyst Blog',
                        'description' => $description,
                        'url' => $canonical,
                        'mainEntity' => ['@type' => 'ItemList', 'itemListElement' => $jsonLdItems],
                    ],
                ],
            ],
        ], 'frontend');
        return null;
    }

    // --- Admin ---

    public function adminIndex(): void {
        View::render('admin/categories/index', [
            'title' => 'Categories',
            'activeNav' => 'categories',
            'categories' => $this->categories->all(),
        ], 'admin');
    }

    public function store(): never {
        $errors = Validator::make(Request::all(), ['name' => 'required|max:120']);
        if ($errors) {
            flash('error', 'Category name is required.');
        } else {
            $this->categories->create(['name' => Request::input('name'), 'description' => Request::input('description'), 'color' => Request::input('color', '#0F4077')], (int) auth_user()['id']);
            flash('success', 'Category created.');
        }
        Response::redirect(url('/dashboard/categories'));
    }

    public function update(int $id): never {
        $data = array_filter([
            'name' => Request::input('name'),
            'description' => Request::input('description'),
            'color' => Request::input('color'),
        ], fn ($v) => $v !== null && $v !== '');
        $this->categories->update($id, $data, (int) auth_user()['id']);
        flash('success', 'Category updated.');
        Response::redirect(url('/dashboard/categories'));
    }

    public function destroy(int $id): never {
        $this->categories->delete($id, (int) auth_user()['id']);
        flash('success', 'Category deleted.');
        Response::redirect(url('/dashboard/categories'));
    }
}
