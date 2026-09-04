<?php
declare(strict_types=1);

namespace Skoolyst\Controllers\Api;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Models\Post;
use Skoolyst\Services\CommentService;

class CommentController {
    public function __construct(private CommentService $comments = new CommentService()) {}

    public function store(string $slug): never {
        $post = (new Post())->findBySlug($slug);
        if (!$post) {
            Response::json(['error' => 'Post not found'], 404);
        }

        $errors = Validator::make(Request::all(), [
            'author_name' => 'required|max:120',
            'author_email' => 'required|email',
            'body' => 'required',
        ]);
        if ($errors) {
            Response::json(['error' => 'Validation failed', 'errors' => $errors], 422);
        }

        $id = $this->comments->submit((int) $post['id'], (string) Request::input('author_name'), (string) Request::input('author_email'), (string) Request::input('body'));
        Response::json(['data' => ['id' => $id, 'status' => 'pending']], 201);
    }
}
