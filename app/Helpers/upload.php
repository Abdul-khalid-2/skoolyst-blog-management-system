<?php
// Centralized upload validation: MIME, extension, size, filename normalization and storage path.

const UPLOAD_ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
const UPLOAD_ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

/**
 * Validate and store an uploaded file (one entry from $_FILES) under
 * uploads/{$subdir}/ (project root, kept outside public/ on purpose — see
 * MediaController@serve, which streams these through a controlled route
 * instead of exposing the directory directly).
 *
 * @throws RuntimeException on any validation failure.
 * @return array{filename: string, original_name: string, size_label: string}
 */
function handle_upload(array $file, string $subdir): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please try again.');
    }

    $maxSize = (int) (require dirname(__DIR__, 2) . '/config/upload.php')['max_size'];
    if ($file['size'] > $maxSize) {
        throw new RuntimeException('File is too large (max ' . round($maxSize / 1048576, 1) . ' MB).');
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, UPLOAD_ALLOWED_EXTENSIONS, true)) {
        throw new RuntimeException('Unsupported file type.');
    }

    $mime = mime_content_type($file['tmp_name']) ?: '';
    if (!in_array($mime, UPLOAD_ALLOWED_MIME_TYPES, true)) {
        throw new RuntimeException('Unsupported file type.');
    }

    $dir = dirname(__DIR__, 2) . '/uploads/' . trim($subdir, '/');
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    return [
        'filename' => $filename,
        'original_name' => $file['name'],
        'size_label' => format_bytes((int) $file['size']),
    ];
}
