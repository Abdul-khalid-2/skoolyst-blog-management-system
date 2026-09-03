<?php
declare(strict_types=1);

require __DIR__ . '/autoload.php';

use Dotenv\Dotenv;

$root = dirname(__DIR__);

if (file_exists($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

require_once __DIR__ . '/helpers.php';
