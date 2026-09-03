<?php
declare(strict_types=1);

namespace Skoolyst\Services;

use Skoolyst\Models\AuditLog;
use Skoolyst\Models\Media;

class MediaService {
    public function __construct(
        private Media $media = new Media(),
        private AuditLog $audit = new AuditLog(),
    ) {}

    public function recent(int $limit = 60): array {
        return $this->media->recent($limit);
    }

    /** $file is one entry from $_FILES. Returns the new blog_media row's id. */
    public function upload(array $file, int $userId): int {
        $stored = handle_upload($file, 'media');
        $id = $this->media->create([
            'name' => $stored['original_name'],
            'url' => url('media/' . $stored['filename']),
            'size' => $stored['size_label'],
            'uploaded_by' => $userId,
        ]);
        $this->audit->record($userId, 'media.uploaded', 'media', $id, ['name' => $stored['original_name']]);
        return $id;
    }

    public function delete(int $id, int $userId): bool {
        $item = $this->media->find($id);
        if ($item) {
            $path = dirname(__DIR__, 2) . '/uploads/media/' . basename(parse_url($item['url'], PHP_URL_PATH));
            if (is_file($path)) @unlink($path);
        }
        $ok = $this->media->delete($id);
        $this->audit->record($userId, 'media.deleted', 'media', $id);
        return $ok;
    }
}
