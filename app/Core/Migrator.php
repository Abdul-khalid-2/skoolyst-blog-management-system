<?php
declare(strict_types=1);

namespace Skoolyst\Core;

/**
 * Minimal migration runner. Tracks applied migrations in `blog_migrations`
 * (name matches this module's `blog_` table-naming convention) and runs any
 * database/migrations/*.php file not yet recorded, in filename order.
 *
 * Each migration file returns ['up' => 'SQL...', 'down' => 'SQL...'].
 */
class Migrator {
    private const TABLE = 'blog_migrations';
    private const DIR = __DIR__ . '/../../database/migrations';

    public static function ensureMigrationsTable(): void {
        Database::connection()->exec(
            'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(191) NOT NULL UNIQUE,
                batch INT UNSIGNED NOT NULL,
                run_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    /** Names (filenames without extension) of migrations already applied. */
    public static function applied(): array {
        self::ensureMigrationsTable();
        $stmt = Database::connection()->query('SELECT migration FROM ' . self::TABLE);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /** Filenames (without .php) of every migration file, in run order. */
    public static function all(): array {
        $files = glob(self::DIR . '/*.php') ?: [];
        sort($files);
        return array_map(fn ($f) => basename($f, '.php'), $files);
    }

    public static function pending(): array {
        return array_values(array_diff(self::all(), self::applied()));
    }

    /**
     * Run every pending migration. Returns the names run.
     * Note: DDL statements (CREATE/DROP TABLE) cause an implicit commit in
     * MySQL/MariaDB, so transactions don't actually protect these statements —
     * wrapping them in beginTransaction()/rollBack() only produces confusing
     * "no active transaction" errors on failure. Each migration just runs
     * directly; a failure stops the run and reports which migration failed.
     */
    public static function run(): array {
        self::ensureMigrationsTable();
        $pdo = Database::connection();
        $pending = self::pending();
        if (!$pending) return [];

        $batchStmt = $pdo->query('SELECT COALESCE(MAX(batch), 0) FROM ' . self::TABLE);
        $batch = ((int) $batchStmt->fetchColumn()) + 1;

        $insert = $pdo->prepare('INSERT INTO ' . self::TABLE . ' (migration, batch, run_at) VALUES (:m, :b, NOW())');
        $ran = [];

        foreach ($pending as $name) {
            $definition = require self::DIR . '/' . $name . '.php';
            try {
                $pdo->exec($definition['up']);
                $insert->execute(['m' => $name, 'b' => $batch]);
                $ran[] = $name;
            } catch (\Throwable $e) {
                throw new \RuntimeException("Migration failed: {$name} — " . $e->getMessage() . ($ran ? ' (already applied in this run: ' . implode(', ', $ran) . ')' : ''), previous: $e);
            }
        }

        return $ran;
    }

    /** Roll back the most recent batch. Returns the names rolled back. */
    public static function rollback(): array {
        self::ensureMigrationsTable();
        $pdo = Database::connection();

        $batchStmt = $pdo->query('SELECT MAX(batch) FROM ' . self::TABLE);
        $batch = $batchStmt->fetchColumn();
        if (!$batch) return [];

        $stmt = $pdo->prepare('SELECT migration FROM ' . self::TABLE . ' WHERE batch = :b ORDER BY id DESC');
        $stmt->execute(['b' => $batch]);
        $names = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $delete = $pdo->prepare('DELETE FROM ' . self::TABLE . ' WHERE migration = :m');
        $rolledBack = [];

        foreach ($names as $name) {
            $definition = require self::DIR . '/' . $name . '.php';
            try {
                $pdo->exec($definition['down']);
                $delete->execute(['m' => $name]);
                $rolledBack[] = $name;
            } catch (\Throwable $e) {
                throw new \RuntimeException("Rollback failed: {$name} — " . $e->getMessage(), previous: $e);
            }
        }

        return $rolledBack;
    }
}
