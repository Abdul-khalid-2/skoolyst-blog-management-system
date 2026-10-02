<?php
declare(strict_types=1);

namespace Skoolyst\Models;

use PDO;
use Skoolyst\Core\Database;

/**
 * Shared user model. Authentication/user data should remain compatible across modules.
 *
 * Only the auth-specific queries this phase needs live here; the generic
 * find/all/save active-record layer for every model is built out in
 * Phase 5 (Database & Models) and this class will adopt it then.
 */
class User {
    public static function findByEmail(string $email): ?array {
        $stmt = Database::connection()->prepare('SELECT * FROM blog_users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function findById(int $id): ?array {
        $stmt = Database::connection()->prepare('SELECT * FROM blog_users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function create(string $name, string $email, string $password, string $role = 'author'): int {
        $stmt = Database::connection()->prepare(
            'INSERT INTO blog_users (name, email, password, role, created_at, updated_at) VALUES (:name, :email, :password, :role, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public static function findBySkoolystId(int $skoolystId): ?array {
        $stmt = Database::connection()->prepare('SELECT * FROM blog_users WHERE skoolyst_id = :sid LIMIT 1');
        $stmt->execute(['sid' => $skoolystId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Account provisioned by "Login with Skoolyst". It gets a random, never-disclosed
     * password so the NOT NULL column holds and password login simply can't succeed —
     * the user always signs in through skoolyst.com.
     */
    public static function createFromSkoolyst(int $skoolystId, string $name, string $email, string $role): int {
        $id = self::create($name, $email, bin2hex(random_bytes(32)), $role);
        self::update($id, ['skoolyst_id' => $skoolystId]);
        return $id;
    }

    public static function findByGoogleId(string $googleId): ?array {
        $stmt = Database::connection()->prepare('SELECT * FROM blog_users WHERE google_id = :gid LIMIT 1');
        $stmt->execute(['gid' => $googleId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /** Account provisioned by "Continue with Google" — random undisclosed password, same reasoning as createFromSkoolyst(). */
    public static function createFromGoogle(string $googleId, string $name, string $email, string $role): int {
        $id = self::create($name, $email, bin2hex(random_bytes(32)), $role);
        self::update($id, ['google_id' => $googleId]);
        return $id;
    }

    /** Update a user's own fillable fields (e.g. name, password — the password value passed in must already be hashed). */
    public static function update(int $id, array $data): bool {
        if (!$data) return false;
        $set = implode(', ', array_map(fn ($c) => "{$c} = :{$c}", array_keys($data))) . ', updated_at = NOW()';
        $data['id'] = $id;
        $stmt = Database::connection()->prepare("UPDATE blog_users SET {$set} WHERE id = :id");
        return $stmt->execute($data);
    }

    /** id/name of every staff account (admin/editor/author), for the dashboard's author filter dropdowns. */
    public static function staffList(): array {
        $stmt = Database::connection()->query(
            "SELECT id, name FROM blog_users WHERE role IN ('admin','editor','author') ORDER BY name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** id/name/email of active accounts with any of $roles — recipients for staff email notifications. */
    public static function activeWithRoles(array $roles): array {
        if (!$roles) return [];
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $stmt = Database::connection()->prepare(
            "SELECT id, name, email FROM blog_users WHERE active = 1 AND role IN ({$placeholders})"
        );
        $stmt->execute(array_values($roles));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Every account, for the admin-only Users management page. */
    public static function all(): array {
        $stmt = Database::connection()->query('SELECT * FROM blog_users ORDER BY role, name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete(int $id): bool {
        $stmt = Database::connection()->prepare('DELETE FROM blog_users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public static function touchLastLogin(int $id): void {
        $stmt = Database::connection()->prepare('UPDATE blog_users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** Strip sensitive columns before putting a user record in the session. */
    public static function forSession(array $user): array {
        unset($user['password']);
        return $user;
    }
}
