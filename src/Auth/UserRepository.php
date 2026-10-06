<?php

declare(strict_types=1);

namespace Team\Auth;

use PDO;

final class UserRepository
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    /** @return list<User> */
    public function activeUsers(): array
    {
        $rows = $this->connection
            ->query(
                'SELECT id, name, role, password_hash
                 FROM users
                 WHERE activated_at IS NOT NULL
                 ORDER BY id',
            )
            ->fetchAll();

        return array_map($this->map(...), $rows);
    }

    public function findById(int $id): ?User
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, role, password_hash
             FROM users
             WHERE id = :id AND activated_at IS NOT NULL
             LIMIT 1',
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $this->map($row) : null;
    }

    /** @param array<string, mixed> $row */
    private function map(array $row): User
    {
        return new User(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['role'],
            (string) $row['password_hash'],
        );
    }
}
