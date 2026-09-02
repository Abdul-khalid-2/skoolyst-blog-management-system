<?php
function flash(string $key, mixed $value = null): mixed { if ($value !== null) return $_SESSION['_flash'][$key] = $value; $v = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $v; }
