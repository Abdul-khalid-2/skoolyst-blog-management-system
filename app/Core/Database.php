<?php
declare(strict_types=1);

namespace Skoolyst\Core;

use PDO;

class Database {
    private static ?PDO $connection = null;

    public static function connection(): PDO {
        if (!self::$connection) {
            self::$connection = new PDO(
                'mysql:host=' . ($_ENV['DB_HOST'] ?? '127.0.0.1') .
                ';port=' . ($_ENV['DB_PORT'] ?? '3306') .
                ';dbname=' . ($_ENV['DB_DATABASE'] ?? '') . ';charset=utf8mb4',
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return self::$connection;
    }
}
