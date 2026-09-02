<?php
function old(string $key, mixed $default = ''): mixed { return $_POST[$key] ?? $default; }
function clean(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
