<?php

require_once __DIR__ . '/../models/Task.php';

class TaskController
{
    public static function getAll(PDO $pdo, array $filters = []): array
    {
        $sql = 'SELECT * FROM tasks WHERE 1 = 1';
        $params = [];

        if (!empty($filters['status'])) {
            $status = trim((string) $filters['status']);
            if (!in_array($status, Task::VALID_STATUSES, true)) {
                throw new InvalidArgumentException('Invalid status filter.');
            }
            $sql .= ' AND status = :status';
            $params[':status'] = $status;
        }

        if (!empty($filters['priority'])) {
            $priority = trim((string) $filters['priority']);
            if (!in_array($priority, Task::VALID_PRIORITIES, true)) {
                throw new InvalidArgumentException('Invalid priority filter.');
            }
            $sql .= ' AND priority = :priority';
            $params[':priority'] = $priority;
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim((string) $filters['search']) . '%';
            $sql .= ' AND (title LIKE :searchTerm OR description LIKE :searchTermAlt)';
            $params[':searchTerm'] = $search;
            $params[':searchTermAlt'] = $search;
        }

        $sql .= ' ORDER BY createdAt DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function getById(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $task = $stmt->fetch();

        return $task ?: null;
    }

    public static function create(PDO $pdo, array $payload): array
    {
        $validation = Task::validatePayload($payload, false);
        if (!$validation['valid']) {
            throw new InvalidArgumentException('Validation failed');
        }

        $data = $validation['data'];
        $stmt = $pdo->prepare(
            'INSERT INTO tasks (title, description, status, priority) VALUES (:title, :description, :status, :priority)'
        );
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':status' => $data['status'],
            ':priority' => $data['priority'],
        ]);

        $newId = (int) $pdo->lastInsertId();
        $created = self::getById($pdo, $newId);

        return $created ?? [];
    }

    public static function update(PDO $pdo, int $id, array $payload): ?array
    {
        $existing = self::getById($pdo, $id);
        if (!$existing) {
            return null;
        }

        $validation = Task::validatePayload($payload, true);
        if (!$validation['valid']) {
            throw new InvalidArgumentException('Validation failed');
        }

        $updates = [];
        $params = [':id' => $id];
        $normalized = $validation['data'];

        foreach (['title', 'description', 'status', 'priority'] as $field) {
            if (!array_key_exists($field, $normalized)) {
                continue;
            }

            $updates[] = $field . ' = :' . $field;
            $params[':' . $field] = $normalized[$field];
        }

        if (empty($updates)) {
            return $existing;
        }

        $sql = 'UPDATE tasks SET ' . implode(', ', $updates) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return self::getById($pdo, $id);
    }

    public static function delete(PDO $pdo, int $id): bool
    {
        $existing = self::getById($pdo, $id);
        if (!$existing) {
            return false;
        }

        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return true;
    }

    public static function getStats(PDO $pdo): array
    {
        $sql = 'SELECT
                    COUNT(*) AS totalTasks,
                    SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) AS pendingTasks,
                    SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) AS completedTasks,
                    SUM(CASE WHEN priority = "High" THEN 1 ELSE 0 END) AS highPriorityTasks
                FROM tasks';

        $stmt = $pdo->query($sql);
        return $stmt->fetch() ?: [
            'totalTasks' => 0,
            'pendingTasks' => 0,
            'completedTasks' => 0,
            'highPriorityTasks' => 0,
        ];
    }
}
