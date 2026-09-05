<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Models\Media;
use Skoolyst\Models\User;
use Skoolyst\Services\MediaService;

class MediaController {
    public function __construct(private MediaService $media = new MediaService()) {}

    public function index(): void {
        $user = auth_user();
        $authorId = ($user['role'] ?? '') === 'author' ? (int) $user['id'] : null;

        \Skoolyst\Core\View::render('admin/media/index', [
            'title' => 'Media Library',
            'activeNav' => 'media',
            'items' => $this->media->recent(60, $authorId),
            'uploaders' => $authorId === null ? User::staffList() : [],
        ], 'admin');
    }

    public function upload(): never {
        $file = $_FILES['file'] ?? null;
        if (!$file) {
            flash('error', 'Choose a file to upload.');
        } else {
            try {
                $this->media->upload($file, (int) auth_user()['id']);
                flash('success', 'File uploaded.');
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
            } catch (\Throwable $e) {
                flash('error', 'Could not process the uploaded image. Please try again.');
            }
        }
        Response::redirect(url('/dashboard/media'));
    }

    public function destroy(int $id): never {
        $item = (new Media())->find($id);
        $user = auth_user();
        if ($item && $this->media->canManage($item, (int) $user['id'], (string) $user['role'])) {
            $this->media->delete($id, (int) $user['id']);
            flash('success', 'File deleted.');
        } else {
            flash('error', 'You can only manage your own uploads.');
        }
        Response::redirect(url('/dashboard/media'));
    }

    /**
     * Stream a file from uploads/media/. Uploads are kept outside public/ on
     * purpose (see app/Helpers/upload.php) — this is the only path to them.
     */
    public function serve(string $filename): never {
        $filename = basename($filename); // defend against path traversal
        $path = dirname(__DIR__, 2) . '/uploads/media/' . $filename;

        // Every upload is re-encoded to .webp by handle_upload() — anything else
        // in this directory isn't a file we ever produced, so refuse to serve it.
        if (!str_ends_with($filename, '.webp') || !is_file($path)) {
            http_response_code(404);
            exit('Not found');
        }

        header('Content-Type: image/webp');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($path);
        exit;
    }
}
