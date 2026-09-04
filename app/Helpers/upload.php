<?php
// Centralized upload validation for images.
//
// Security model: we don't trust the file extension or the client-supplied
// Content-Type at all. getimagesize() parses the actual file header to prove
// it's a real, decodable image, and then we re-encode every upload to WebP
// through GD rather than storing the original bytes. That re-encode step is
// what actually matters — it only ever writes back the pixel data GD itself
// decoded, so anything else smuggled inside the file (a polyglot payload,
// embedded script, trailing junk after the image data, EXIF metadata) is
// discarded rather than stored and served back to visitors. The output is
// always a fresh, randomly-named .webp file — never the uploaded file itself.

/**
 * Validate and store an uploaded image (one entry from $_FILES) under
 * uploads/{$subdir}/ as a freshly re-encoded WebP file.
 *
 * @throws RuntimeException on any validation failure.
 * @return array{filename: string, original_name: string, size_label: string}
 */
function handle_upload(array $file, string $subdir): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please try again.');
    }
    if (!is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new RuntimeException('Invalid upload.');
    }

    $maxSize = (int) (require dirname(__DIR__, 2) . '/config/upload.php')['max_size'];
    if ($file['size'] > $maxSize) {
        throw new RuntimeException('File is too large (max ' . round($maxSize / 1048576, 1) . ' MB).');
    }

    // The real gate: does this decode as an actual image, and as one of the
    // formats we know how to re-encode? Extension and claimed MIME type are
    // both attacker-controlled and never consulted.
    $info = @getimagesize($file['tmp_name']);
    if (!$info || $info[0] < 1 || $info[1] < 1) {
        throw new RuntimeException('That file is not a valid image.');
    }

    $image = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($file['tmp_name']),
        IMAGETYPE_PNG => @imagecreatefrompng($file['tmp_name']),
        IMAGETYPE_GIF => @imagecreatefromgif($file['tmp_name']),
        IMAGETYPE_WEBP => @imagecreatefromwebp($file['tmp_name']),
        IMAGETYPE_BMP => @imagecreatefrombmp($file['tmp_name']),
        default => null,
    };
    if (!$image) {
        throw new RuntimeException('Unsupported image type. Use JPEG, PNG, GIF, WebP or BMP.');
    }

    // Preserve transparency instead of it turning black when re-encoded.
    imagepalettetotruecolor($image);
    imagealphablending($image, true);
    imagesavealpha($image, true);

    $dir = dirname(__DIR__, 2) . '/uploads/' . trim($subdir, '/');
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = bin2hex(random_bytes(16)) . '.webp';
    $path = $dir . '/' . $filename;
    $saved = imagewebp($image, $path, 82);
    imagedestroy($image);

    if (!$saved) {
        throw new RuntimeException('Could not process the uploaded image.');
    }

    return [
        'filename' => $filename,
        'original_name' => pathinfo($file['name'], PATHINFO_FILENAME) . '.webp',
        'size_label' => format_bytes((int) filesize($path)),
    ];
}
