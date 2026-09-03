<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Core\View;
use Skoolyst\Models\Category;
use Skoolyst\Services\CommentService;
use Skoolyst\Services\PostService;

class PostController {
    public function __construct(
        private PostService $posts = new PostService(),
        private CommentService $comments = new CommentService(),
        private Category $categories = new Category(),
    ) {}

    public function home(): void {
        $sections = $this->posts->forHomepage();
        View::render('frontend/home', [
            'title' => 'Skoolyst Blog — Home',
            'description' => 'Product news, teaching resources and community stories from Skoolyst.',
            'activeNav' => 'home',
            'featured' => $sections['featured'],
            'latest' => $sections['latest'],
        ], 'frontend');
    }

    public function index(): void {
        $page = max(1, (int) Request::query('page', 1));
        $search = trim((string) Request::query('q', ''));
        $categorySlug = (string) Request::query('category', '');
        $sort = (string) Request::query('sort', 'newest');

        $category = $categorySlug ? $this->categories->findBySlug($categorySlug) : null;
        $result = $this->posts->publicList($page, $search ?: null, $category['id'] ?? null, $sort);

        View::render('frontend/blog', [
            'title' => 'Articles — Skoolyst Blog',
            'description' => 'Browse all articles from the Skoolyst blog.',
            'activeNav' => 'blog',
            'posts' => $result['data'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'search' => $search,
            'sort' => $sort,
            'categories' => $this->categories->all('name ASC'),
            'activeCategory' => $categorySlug,
        ], 'frontend');
    }

    public function show(string $slug): mixed {
        $post = $this->posts->bySlug($slug);
        if (!$post) {
            http_response_code(404);
            return View::render('errors/404', [], 'frontend');
        }

        $category = $post['category_id'] ? (new Category())->find((int) $post['category_id']) : null;
        $author = $post['author_id'] ? \Skoolyst\Models\User::findById((int) $post['author_id']) : null;

        View::render('frontend/post', [
            'title' => $post['seo_title'] ?: $post['title'],
            'description' => $post['seo_description'] ?: $post['excerpt'],
            'canonical' => url('/post/' . $post['slug']),
            'ogImage' => $post['cover_image'],
            'activeNav' => 'blog',
            'post' => $post,
            'category' => $category,
            'author' => $author,
            'tags' => $this->posts->tagsFor((int) $post['id']),
            'comments' => $this->comments->approvedForPost((int) $post['id']),
        ], 'frontend');
        return null;
    }

    // --- Admin ---

    public function adminIndex(): void {
        $page = max(1, (int) Request::query('page', 1));
        $result = $this->posts->dashboardList($page);
        View::render('admin/posts/index', [
            'title' => 'Posts',
            'activeNav' => 'posts',
            'posts' => $result['data'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
        ], 'admin');
    }

    public function create(): void {
        View::render('admin/posts/edit', [
            'title' => 'New Post',
            'activeNav' => 'posts',
            'post' => null,
            'categories' => $this->categories->all('name ASC'),
            'allTags' => (new \Skoolyst\Models\Tag())->all('name ASC'),
            'selectedTagIds' => [],
        ], 'admin');
    }

    public function store(): mixed {
        $errors = Validator::make(Request::all(), [
            'title' => 'required|max:220',
            'body' => 'required',
            'status' => 'required|in:draft,published',
        ]);

        if ($errors) {
            flash('error', 'Please fix the errors below.');
            return View::render('admin/posts/edit', [
                'title' => 'New Post', 'activeNav' => 'posts', 'post' => Request::all(),
                'categories' => $this->categories->all('name ASC'), 'errors' => $errors,
                'allTags' => (new \Skoolyst\Models\Tag())->all('name ASC'), 'selectedTagIds' => [],
            ], 'admin');
        }

        $id = $this->posts->create([
            'title' => Request::input('title'),
            'slug' => trim((string) Request::input('slug', '')),
            'excerpt' => Request::input('excerpt'),
            'body' => Request::input('body'),
            'cover_image' => Request::input('cover_image'),
            'category_id' => Request::input('category_id') ?: null,
            'status' => Request::input('status'),
            'seo_title' => Request::input('seo_title'),
            'seo_description' => Request::input('seo_description'),
        ], (int) auth_user()['id']);
        $this->posts->syncTagsFromEditor($id, (array) Request::input('tags', []), (string) Request::input('new_tags', ''));

        flash('success', 'Post created.');
        return Response::redirect(url('/dashboard/posts/' . $id . '/edit'));
    }

    public function edit(int $id): mixed {
        $post = (new \Skoolyst\Models\Post())->find($id);
        if (!$post) return Response::redirect(url('/dashboard/posts'));

        View::render('admin/posts/edit', [
            'title' => 'Edit Post',
            'activeNav' => 'posts',
            'post' => $post,
            'categories' => $this->categories->all('name ASC'),
            'allTags' => (new \Skoolyst\Models\Tag())->all('name ASC'),
            'selectedTagIds' => array_column($this->posts->tagsFor($id), 'id'),
        ], 'admin');
        return null;
    }

    public function update(int $id): mixed {
        $errors = Validator::make(Request::all(), [
            'title' => 'required|max:220',
            'body' => 'required',
            'status' => 'required|in:draft,published',
        ]);

        if ($errors) {
            flash('error', 'Please fix the errors below.');
            return Response::redirect(url('/dashboard/posts/' . $id . '/edit'));
        }

        $this->posts->update($id, [
            'title' => Request::input('title'),
            'slug' => trim((string) Request::input('slug', '')),
            'excerpt' => Request::input('excerpt'),
            'body' => Request::input('body'),
            'cover_image' => Request::input('cover_image'),
            'category_id' => Request::input('category_id') ?: null,
            'status' => Request::input('status'),
            'seo_title' => Request::input('seo_title'),
            'seo_description' => Request::input('seo_description'),
        ], (int) auth_user()['id']);
        $this->posts->syncTagsFromEditor($id, (array) Request::input('tags', []), (string) Request::input('new_tags', ''));

        flash('success', 'Post updated.');
        return Response::redirect(url('/dashboard/posts/' . $id . '/edit'));
    }

    public function destroy(int $id): never {
        $this->posts->delete($id, (int) auth_user()['id']);
        flash('success', 'Post deleted.');
        Response::redirect(url('/dashboard/posts'));
    }
}
