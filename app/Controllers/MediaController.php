<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Services\MediaService;

class MediaController {
    public function __construct(private MediaService $media = new MediaService()) {}

    public function index(): void {
        \Skoolyst\Core\View::render('admin/media/index', [
            'title' => 'Media Library',
            'activeNav' => 'media',
            'items' => $this->media->recent(),
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
            }
        }
        Response::redirect(url('/dashboard/media'));
    }

    public function destroy(int $id): never {
        $this->media->delete($id, (int) auth_user()['id']);
        flash('success', 'File deleted.');
        Response::redirect(url('/dashboard/media'));
    }

    /**
     * Stream a file from uploads/media/. Uploads are kept outside public/ on
     * purpose (see app/Helpers/upload.php) — this is the only path to them.
     */
    public function serve(string $filename): never {
        $filename = basename($filename); // defend against path traversal
        $path = dirname(__DIR__, 2) . '/uploads/media/' . $filename;

        if (!is_file($path)) {
            http_response_code(404);
            exit('Not found');
        }

        header('Content-Type: ' . (mime_content_type($path) ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($path);
        exit;
    }
}
