<?php
declare(strict_types=1);

namespace Skoolyst\Core;

use PDO;

/**
 * Lightweight active-record base. Subclasses set $table/$primaryKey/$fillable
 * and inherit generic CRUD; add model-specific query methods on top as needed.
 * Rows are returned as plain associative arrays, matching this blueprint's
 * simple style (no object hydration).
 */
abstract class Model {
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    protected function pdo(): PDO {
        return Database::connection();
    }

    public function all(?string $orderBy = null): array {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        return $this->pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int|string $id): ?array {
        $stmt = $this->pdo()->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * $conditions is column => value (combined with AND, exact match only).
     * For anything more complex (LIKE, OR, joins), write a dedicated method
     * on the subclass using $this->pdo() directly.
     */
    public function where(array $conditions, ?string $orderBy = null, ?int $limit = null): array {
        [$clause, $params] = $this->buildWhere($conditions);
        $sql = "SELECT * FROM {$this->table}" . ($clause ? " WHERE {$clause}" : '');
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        if ($limit) $sql .= " LIMIT " . (int) $limit;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(array $conditions = []): int {
        [$clause, $params] = $this->buildWhere($conditions);
        $sql = "SELECT COUNT(*) FROM {$this->table}" . ($clause ? " WHERE {$clause}" : '');
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** Insert $data (filtered to $fillable) and return the new row's id. */
    public function create(array $data): int {
        $data = $this->filterFillable($data);
        $columns = array_keys($data);
        $placeholders = array_map(fn ($c) => ':' . $c, $columns);
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($data);
        return (int) $this->pdo()->lastInsertId();
    }

    /** Update the row matching $id with $data (filtered to $fillable). */
    public function update(int|string $id, array $data): bool {
        $data = $this->filterFillable($data);
        if (!$data) return false;
        $set = implode(', ', array_map(fn ($c) => "{$c} = :{$c}", array_keys($data)));
        $data['__id'] = $id;
        $stmt = $this->pdo()->prepare("UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = :__id");
        return $stmt->execute($data);
    }

    public function delete(int|string $id): bool {
        $stmt = $this->pdo()->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Simple offset-based pagination.
     * Returns ['data' => array, 'total' => int, 'page' => int, 'perPage' => int, 'totalPages' => int].
     */
    public function paginate(int $page = 1, int $perPage = 10, array $conditions = [], ?string $orderBy = null): array {
        $page = max(1, $page);
        [$clause, $params] = $this->buildWhere($conditions);
        $where = $clause ? " WHERE {$clause}" : '';

        $countStmt = $this->pdo()->prepare("SELECT COUNT(*) FROM {$this->table}{$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT * FROM {$this->table}{$where}";
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        $sql .= ' LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo()->prepare($sql);
        foreach ($params as $key => $value) $stmt->bindValue($key, $value);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    protected function filterFillable(array $data): array {
        return $this->fillable ? array_intersect_key($data, array_flip($this->fillable)) : $data;
    }

    /** Escape hatch for queries the generic helpers above don't cover (LIKE, JOINs, etc.). */
    public function rawQuery(string $sql, array $params = []): array {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rawScalar(string $sql, array $params = []): mixed {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /** @return array{0: string, 1: array} [whereClause, boundParams] */
    protected function buildWhere(array $conditions): array {
        if (!$conditions) return ['', []];
        $clauses = [];
        $params = [];
        foreach ($conditions as $column => $value) {
            $param = 'w_' . $column;
            $clauses[] = "{$column} = :{$param}";
            $params[$param] = $value;
        }
        return [implode(' AND ', $clauses), $params];
    }
}
