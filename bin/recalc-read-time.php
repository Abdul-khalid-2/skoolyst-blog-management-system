<?php
declare(strict_types=1);

/**
 * One-off: recompute read_time_minutes for every post with the Unicode-aware
 * counter (older Urdu posts were saved as "1 min read").
 *   php bin/recalc-read-time.php
 */
require __DIR__ . '/../bootstrap/app.php';

$posts = new \Skoolyst\Models\Post();
$n = 0;
foreach ($posts->all() as $post) {
    $minutes = reading_minutes((string) ($post['body'] ?? ''));
    if ($minutes !== (int) $post['read_time_minutes']) {
        $posts->update((int) $post['id'], ['read_time_minutes' => $minutes]);
        echo "#{$post['id']} {$post['slug']}: {$post['read_time_minutes']} -> {$minutes}\n";
        $n++;
    }
}
echo "Updated {$n} post(s).\n";
