<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Core\View;
use Skoolyst\Models\Post;
use Skoolyst\Services\CommentService;

class CommentController {
    public function __construct(private CommentService $comments = new CommentService()) {}

    // --- Admin ---

    public function adminIndex(): void {
        $pending = $this->comments->pending();
        $posts = new Post();
        $rows = array_map(function ($c) use ($posts) {
            $post = $posts->find((int) $c['post_id']);
            return $c + ['post_title' => $post['title'] ?? 'Deleted post', 'post_slug' => $post['slug'] ?? null];
        }, $pending);

        View::render('admin/comments/index', [
            'title' => 'Comments',
            'activeNav' => 'comments',
            'comments' => $rows,
        ], 'admin');
    }

    public function approve(int $id): mixed {
        $this->comments->approve($id, (int) auth_user()['id']);
        flash('success', 'Comment approved.');
        return Response::redirect(url('/dashboard/comments'));
    }

    public function reject(int $id): mixed {
        $this->comments->reject($id, (int) auth_user()['id']);
        flash('success', 'Comment rejected.');
        return Response::redirect(url('/dashboard/comments'));
    }

    // --- Public ---

    public function store(string $slug): never {
        $post = (new Post())->findBySlug($slug);

        $errors = $post ? Validator::make(Request::all(), [
            'author_name' => 'required|max:120',
            'author_email' => 'required|email',
            'body' => 'required',
        ]) : ['post' => ['Post not found.']];

        if ($errors) {
            flash('error', 'Please fill in all comment fields correctly.');
        } else {
            $this->comments->submit((int) $post['id'], (string) Request::input('author_name'), (string) Request::input('author_email'), (string) Request::input('body'));
            flash('success', 'Thanks! Your comment is awaiting moderation.');
        }

        Response::redirect(url('/post/' . $slug));
    }
}
