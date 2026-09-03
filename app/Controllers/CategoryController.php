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

        View::render('frontend/category', [
            'title' => $category['name'] . ' — Skoolyst Blog',
            'activeNav' => 'blog',
            'category' => $category,
            'posts' => $result['data'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
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
        $this->categories->update($id, [
            'name' => Request::input('name'),
            'description' => Request::input('description'),
            'color' => Request::input('color'),
        ], (int) auth_user()['id']);
        flash('success', 'Category updated.');
        Response::redirect(url('/dashboard/categories'));
    }

    public function destroy(int $id): never {
        $this->categories->delete($id, (int) auth_user()['id']);
        flash('success', 'Category deleted.');
        Response::redirect(url('/dashboard/categories'));
    }
}
