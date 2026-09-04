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
            'related' => $this->posts->relatedTo($post, 3),
            'comments' => $this->comments->approvedForPost((int) $post['id']),
        ], 'frontend');
        return null;
    }

    // --- Admin ---

    public function adminIndex(): void {
        $page = max(1, (int) Request::query('page', 1));
        $user = auth_user();
        $authorId = ($user['role'] ?? '') === 'author' ? (int) $user['id'] : null;

        // author_id is only ever honored as a *filter* when $authorId (the hard ownership
        // scope) is null — an 'author' account can't widen its own view via the query string.
        $filters = [
            'search' => trim((string) Request::query('q', '')),
            'status' => (string) Request::query('status', ''),
            'author_id' => $authorId === null ? (string) Request::query('author_id', '') : '',
        ];

        $result = $this->posts->dashboardList($page, $authorId, $filters);
        View::render('admin/posts/index', [
            'title' => 'Posts',
            'activeNav' => 'posts',
            'posts' => $result['data'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'filters' => $filters,
            'authors' => $authorId === null ? \Skoolyst\Models\User::staffList() : [],
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
            'category_id' => $this->validCategoryId(Request::input('category_id')),
            'status' => Request::input('status'),
            'seo_title' => Request::input('seo_title'),
            'seo_description' => Request::input('seo_description'),
        ], (int) auth_user()['id']);
        $this->posts->syncTagsFromEditor($id, (array) Request::input('tags', []), (string) Request::input('new_tags', ''));

        flash('success', 'Post created.');
        return Response::redirect(url('/dashboard/posts'));
    }

    public function edit(int $id): mixed {
        $post = (new \Skoolyst\Models\Post())->find($id);
        if (!$post) return Response::redirect(url('/dashboard/posts'));
        if (!$this->authorizePostAccess($post)) return Response::redirect(url('/dashboard/posts'));

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
        $existing = (new \Skoolyst\Models\Post())->find($id);
        if (!$existing) return Response::redirect(url('/dashboard/posts'));
        if (!$this->authorizePostAccess($existing)) return Response::redirect(url('/dashboard/posts'));

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
            'category_id' => $this->validCategoryId(Request::input('category_id')),
            'status' => Request::input('status'),
            'seo_title' => Request::input('seo_title'),
            'seo_description' => Request::input('seo_description'),
        ], (int) auth_user()['id']);
        $this->posts->syncTagsFromEditor($id, (array) Request::input('tags', []), (string) Request::input('new_tags', ''));

        flash('success', 'Post updated.');
        return Response::redirect(url('/dashboard/posts/' . $id . '/edit'));
    }

    /** Guards against a stale/tampered category_id (e.g. the category was deleted while the form was open) causing a DB-level FK error. */
    private function validCategoryId(mixed $categoryId): ?int {
        $categoryId = $categoryId ? (int) $categoryId : null;
        return $categoryId && $this->categories->find($categoryId) ? $categoryId : null;
    }

    /** 'author' accounts may only manage their own posts; editor/admin manage all. Flashes and returns false when denied. */
    private function authorizePostAccess(array $post): bool {
        $user = auth_user();
        if ($this->posts->canManage($post, (int) $user['id'], (string) $user['role'])) return true;
        flash('error', 'You can only manage your own posts.');
        return false;
    }

    public function destroy(int $id): never {
        $post = (new \Skoolyst\Models\Post())->find($id);
        if ($post && $this->authorizePostAccess($post)) {
            $this->posts->delete($id, (int) auth_user()['id']);
            flash('success', 'Post deleted.');
        }
        Response::redirect(url('/dashboard/posts'));
    }
}
