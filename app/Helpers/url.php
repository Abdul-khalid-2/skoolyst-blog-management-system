<?php
function url(string $path = ''): string { return rtrim($_ENV['APP_URL'] ?? '', '/') . '/' . ltrim($path, '/'); }
