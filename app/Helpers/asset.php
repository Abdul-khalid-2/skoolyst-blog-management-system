<?php
declare(strict_types=1);

/**
 * Like url(), but appends the file's mtime on disk as a cache-busting query
 * string, so a browser (or a long-lived Cache-Control) only ever serves a
 * stale copy until the file actually changes — no manual ?v= bumps needed.
 */
function asset(string $path): string {
    $fsPath = dirname(__DIR__, 2) . '/public/' . ltrim($path, '/');
    $version = is_file($fsPath) ? (string) filemtime($fsPath) : '0';
    return url($path) . '?v=' . $version;
}
