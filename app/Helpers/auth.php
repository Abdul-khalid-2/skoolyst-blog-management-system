<?php
function auth_user(): ?array { return $_SESSION['user'] ?? null; }
function is_authenticated(): bool { return auth_user() !== null; }
