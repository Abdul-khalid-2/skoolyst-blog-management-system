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
        $user = auth_user();
        $authorId = ($user['role'] ?? '') === 'author' ? (int) $user['id'] : null;

        View::render('admin/comments/index', [
            'title' => 'Comments',
            'activeNav' => 'comments',
            'comments' => $this->comments->pending($authorId),
        ], 'admin');
    }

    public function approve(int $id): mixed {
        if (!$this->authorizeCommentAccess($id)) return Response::redirect(url('/dashboard/comments'));
        $this->comments->approve($id, (int) auth_user()['id']);
        flash('success', 'Comment approved.');
        return Response::redirect(url('/dashboard/comments'));
    }

    public function reject(int $id): mixed {
        if (!$this->authorizeCommentAccess($id)) return Response::redirect(url('/dashboard/comments'));
        $this->comments->reject($id, (int) auth_user()['id']);
        flash('success', 'Comment rejected.');
        return Response::redirect(url('/dashboard/comments'));
    }

    /** 'author' accounts may only approve/reject comments on their own posts; editor/admin manage all. */
    private function authorizeCommentAccess(int $id): bool {
        $comment = $this->comments->findWithPostAuthor($id);
        $user = auth_user();
        if ($comment && $this->comments->canManage($comment, (int) $user['id'], (string) $user['role'])) {
            return true;
        }
        flash('error', 'You can only manage comments on your own posts.');
        return false;
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
