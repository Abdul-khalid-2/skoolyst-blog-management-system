<?php
function csrf_token(): string { return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token()) . '">'; }

/** Verify a submitted CSRF token against the session's token (constant-time). */
function csrf_verify(): bool {
    $submitted = $_POST['_csrf'] ?? \Skoolyst\Core\Request::input('_csrf', '');
    $expected = $_SESSION['_csrf'] ?? null;
    return is_string($submitted) && is_string($expected) && $expected !== '' && hash_equals($expected, $submitted);
}
