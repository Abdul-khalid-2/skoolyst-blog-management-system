<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Models\Post;
use Skoolyst\Services\CommentService;

class CommentController {
    public function __construct(private CommentService $comments = new CommentService()) {}

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
