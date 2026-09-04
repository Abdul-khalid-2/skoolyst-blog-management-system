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

    /** $uploadedBy, when given, restricts the list to that user's own uploads. */
    public function recent(int $limit = 60, ?int $uploadedBy = null): array {
        return $this->media->recent($limit, $uploadedBy);
    }

    /** True if $userRole may manage $item — 'author' accounts may only manage their own uploads; editor/admin manage all. Mirrors PostService::canManage. */
    public function canManage(array $item, int $userId, string $userRole): bool {
        return $userRole !== 'author' || (int) $item['uploaded_by'] === $userId;
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
