<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Core\View;
use Skoolyst\Models\Category;
use Skoolyst\Models\Media;
use Skoolyst\Services\CommentService;
use Skoolyst\Services\MediaService;
use Skoolyst\Services\PostService;

class PostController {
    public function __construct(
        private PostService $posts = new PostService(),
        private CommentService $comments = new CommentService(),
        private Category $categories = new Category(),
        private MediaService $media = new MediaService(),
    ) {}

    public function home(): void {
        $sections = $this->posts->forHomepage();
        View::render('frontend/home', [
            'title' => 'Skoolyst Blog — Home',
            'description' => 'Product news, teaching resources and community stories from Skoolyst.',
            'canonical' => url('/'),
            'activeNav' => 'home',
            'featured' => $sections['featured'],
            'latest' => $sections['latest'],
            'extraJs' => 'assets/js/ads.js',
        ], 'frontend');
    }

    public function index(): void {
        $page = max(1, (int) Request::query('page', 1));
        $search = trim((string) Request::query('q', ''));
        $categorySlug = (string) Request::query('category', '');
        $sort = (string) Request::query('sort', 'newest');

        $category = $categorySlug ? $this->categories->findBySlug($categorySlug) : null;
        $result = $this->posts->publicList($page, $search ?: null, $category['id'] ?? null, $sort);

        // Canonicalizes to the stable category filter only; drops 'q' (search) and
        // 'sort', which would otherwise generate near-duplicate indexable URLs for
        // every query/sort combination of the same underlying content.
        $canonical = url('/blog') . ($categorySlug ? '?category=' . urlencode($categorySlug) : '');

        if ($category) {
            $title = $category['name'] . ' Articles — Skoolyst Blog';
            $description = 'Browse articles about ' . $category['name'] . ' — product news, teaching resources and community stories from Skoolyst.';
        } elseif ($search !== '') {
            $title = 'Search: ' . $search . ' — Skoolyst Blog';
            $description = 'Skoolyst Blog search results for "' . $search . '".';
        } else {
            $title = 'Articles — Skoolyst Blog';
            $description = 'Product news, teaching resources and community stories from the Skoolyst team.';
        }

        $jsonLdItems = array_map(fn ($post, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'item' => ['@type' => 'BlogPosting', 'headline' => $post['title'], 'url' => url('/post/' . $post['slug'])],
        ], $result['data'], array_keys($result['data']));

        View::render('frontend/blog', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'ogImage' => url('assets/images/skoolyst-blog.png'),
            'activeNav' => 'blog',
            'posts' => $result['data'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'search' => $search,
            'sort' => $sort,
            'categories' => $this->categories->all('name ASC'),
            'activeCategory' => $categorySlug,
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => array_values(array_filter([
                            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => url('/blog')],
                            $category ? ['@type' => 'ListItem', 'position' => 3, 'name' => $category['name'], 'item' => $canonical] : null,
                        ])),
                    ],
                    [
                        '@type' => 'CollectionPage',
                        '@id' => $canonical,
                        'name' => $title,
                        'description' => $description,
                        'url' => $canonical,
                        'mainEntity' => ['@type' => 'ItemList', 'itemListElement' => $jsonLdItems],
                    ],
                ],
            ],
        ], 'frontend');
    }

    public function trackView(string $slug): never {
        header('Content-Type: application/json');
        $post = $this->posts->bySlug($slug, false);
        if (!$post || ($post['status'] ?? '') !== 'published') {
            echo json_encode(['views' => 0]);
            exit;
        }
        $this->posts->incrementView((int) $post['id']);
        $newCount = (int) $post['views'] + 1;
        echo json_encode(['views' => $newCount]);
        exit;
    }

    /**
     * Pinged by the reading-time tracker on the post page: it accumulates active
     * seconds client-side and batches them into one request every ~15-25s (plus a
     * final flush on tab hide/close), rather than one request per 5s tick.
     * $seconds is clamped to that window (+buffer) so a tampered/replayed request
     * can't inflate the total beyond what one real flush interval could produce.
     */
    public function trackReadTime(string $slug): never {
        header('Content-Type: application/json');
        $post = $this->posts->bySlug($slug, false);
        if (!$post || ($post['status'] ?? '') !== 'published') {
            echo json_encode(['readSeconds' => 0, 'readMinutes' => 0]);
            exit;
        }
        $seconds = min(30, max(1, (int) Request::input('seconds', 0)));
        $this->posts->trackReadSeconds((int) $post['id'], $seconds);
        $newTotal = (int) $post['read_seconds'] + $seconds;
        echo json_encode(['readSeconds' => $newTotal, 'readMinutes' => (int) round($newTotal / 60)]);
        exit;
    }

    public function show(string $slug): mixed {
        $post = $this->posts->bySlug($slug, false);
        if (!$post) {
            http_response_code(404);
            return View::render('errors/404', [], 'frontend');
        }

        $category = $post['category_id'] ? (new Category())->find((int) $post['category_id']) : null;
        $author = $post['author_id'] ? \Skoolyst\Models\User::findById((int) $post['author_id']) : null;

        $canonical = url('/post/' . $post['slug']);
        View::render('frontend/post', [
            'title' => $post['seo_title'] ?: $post['title'],
            'description' => $post['seo_description'] ?: $post['excerpt'],
            'canonical' => $canonical,
            'ogType' => 'article',
            'ogImage' => $post['cover_image'],
            'activeNav' => 'blog',
            'post' => $post,
            'category' => $category,
            'author' => $author,
            'tags' => $this->posts->tagsFor((int) $post['id']),
            'related' => $this->posts->relatedTo($post, 3),
            'comments' => $this->comments->approvedForPost((int) $post['id']),
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'BlogPosting',
                        'headline' => $post['title'],
                        'description' => $post['seo_description'] ?: $post['excerpt'],
                        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
                        'url' => $canonical,
                        'datePublished' => date('c', strtotime($post['published_date'] ?? $post['created_at'])),
                        'dateModified' => date('c', strtotime($post['updated_at'] ?? $post['created_at'])),
                        'image' => $post['cover_image'] ?: null,
                        // Never fabricate a byline: attribute to the real blog_users author when
                        // one is set, otherwise fall back to the Organization itself (still a
                        // true statement — Skoolyst genuinely published it — not an invented name.
                        'author' => $author
                            ? ['@type' => 'Person', 'name' => $author['name']]
                            : ['@type' => 'Organization', 'name' => 'Skoolyst'],
                        'publisher' => [
                            '@type' => 'Organization',
                            'name' => 'Skoolyst',
                            'logo' => ['@type' => 'ImageObject', 'url' => url('assets/images/skoolyst-blog.png')],
                        ],
                    ],
                    [
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => array_values(array_filter([
                            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => url('/blog')],
                            $category ? ['@type' => 'ListItem', 'position' => 3, 'name' => $category['name'], 'item' => url('/category/' . $category['slug'])] : null,
                            ['@type' => 'ListItem', 'position' => $category ? 4 : 3, 'name' => $post['title'], 'item' => $canonical],
                        ])),
                    ],
                ],
            ],
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
            'maxUploadSize' => $this->maxUploadSize(),
        ], 'admin');
    }

    public function store(): mixed {
        $errors = Validator::make(Request::all(), [
            'title' => 'required|max:220',
            'body' => 'required',
            'status' => 'required|in:draft,published',
        ]);

        [$coverImage, $coverError] = $this->resolveCoverImage((string) Request::input('cover_image', ''));
        if ($coverError) $errors['cover_image_file'][] = $coverError;

        if ($errors) {
            flash('error', 'Please fix the errors below.');
            return View::render('admin/posts/edit', [
                'title' => 'New Post', 'activeNav' => 'posts', 'post' => Request::all(),
                'categories' => $this->categories->all('name ASC'), 'errors' => $errors,
                'allTags' => (new \Skoolyst\Models\Tag())->all('name ASC'), 'selectedTagIds' => [],
                'maxUploadSize' => $this->maxUploadSize(),
            ], 'admin');
        }

        $id = $this->posts->create([
            'title' => Request::input('title'),
            'slug' => trim((string) Request::input('slug', '')),
            'excerpt' => Request::input('excerpt'),
            'body' => Request::input('body'),
            'cover_image' => $coverImage,
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
            'maxUploadSize' => $this->maxUploadSize(),
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

        [$coverImage, $coverError] = $this->resolveCoverImage((string) Request::input('cover_image', $existing['cover_image'] ?? ''));
        if ($coverError) $errors['cover_image_file'][] = $coverError;

        if ($errors) {
            flash('error', 'Please fix the errors below.');
            $post = Request::all();
            $post['id'] = $id;
            if (empty($post['cover_image'])) $post['cover_image'] = $existing['cover_image'] ?? '';
            return View::render('admin/posts/edit', [
                'title' => 'Edit Post', 'activeNav' => 'posts', 'post' => $post,
                'categories' => $this->categories->all('name ASC'), 'errors' => $errors,
                'allTags' => (new \Skoolyst\Models\Tag())->all('name ASC'),
                'selectedTagIds' => array_map('intval', (array) Request::input('tags', [])),
                'maxUploadSize' => $this->maxUploadSize(),
            ], 'admin');
        }

        $this->posts->update($id, [
            'title' => Request::input('title'),
            'slug' => trim((string) Request::input('slug', '')),
            'excerpt' => Request::input('excerpt'),
            'body' => Request::input('body'),
            'cover_image' => $coverImage,
            'category_id' => $this->validCategoryId(Request::input('category_id')),
            'status' => Request::input('status'),
            'seo_title' => Request::input('seo_title'),
            'seo_description' => Request::input('seo_description'),
        ], (int) auth_user()['id']);
        $this->posts->syncTagsFromEditor($id, (array) Request::input('tags', []), (string) Request::input('new_tags', ''));

        flash('success', 'Post updated.');
        return Response::redirect(url('/dashboard/posts/'));
    }

    /**
     * The cover image can come from either tab in the form: an uploaded file (takes
     * priority when present) or a pasted URL. An uploaded file goes through the same
     * secure, WebP-converting pipeline as the Media Library and is tracked there too.
     * @return array{0: string, 1: ?string} [cover image URL, upload error message or null]
     */
    private function resolveCoverImage(string $urlInput): array {
        $file = $_FILES['cover_image_file'] ?? null;
        if (empty($file['name'])) {
            return [trim($urlInput), null];
        }

        try {
            $mediaId = $this->media->upload($file, (int) auth_user()['id']);
            return [(new Media())->find($mediaId)['url'], null];
        } catch (\RuntimeException $e) {
            // Thrown deliberately by handle_upload() with a user-facing message
            // (bad format, too large, too high-resolution, etc).
            return [trim($urlInput), $e->getMessage()];
        } catch (\Throwable $e) {
            // Anything unexpected (e.g. a DB failure while recording the upload)
            // should still surface as a normal validation error, not a 500.
            return [trim($urlInput), 'Could not process the uploaded image. Please try again.'];
        }
    }

    /** The configured upload size cap, exposed to the edit form so it can reject an oversized cover image client-side before ever uploading it. */
    private function maxUploadSize(): int {
        return (int) (require dirname(__DIR__, 2) . '/config/upload.php')['max_size'];
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
