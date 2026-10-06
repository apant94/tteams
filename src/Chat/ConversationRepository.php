<?php

declare(strict_types=1);

namespace Team\Chat;

use PDO;

final class ConversationRepository
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    public function findById(int $id): ?Conversation
    {
        $statement = $this->connection->prepare(
            'SELECT id, admin_id, client_id, created_at, updated_at
             FROM conversations
             WHERE id = :id
             LIMIT 1',
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $this->map($row) : null;
    }

    public function findForClient(int $clientId): ?Conversation
    {
        $statement = $this->connection->prepare(
            'SELECT id, admin_id, client_id, created_at, updated_at
             FROM conversations
             WHERE client_id = :client_id
             LIMIT 1',
        );
        $statement->execute(['client_id' => $clientId]);
        $row = $statement->fetch();

        return is_array($row) ? $this->map($row) : null;
    }

    /** @return list<Conversation> */
    public function findForAdmin(int $adminId): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, admin_id, client_id, created_at, updated_at
             FROM conversations
             WHERE admin_id = :admin_id
             ORDER BY updated_at DESC, id DESC',
        );
        $statement->execute(['admin_id' => $adminId]);

        return array_map($this->map(...), $statement->fetchAll());
    }

    /** @param array<string, mixed> $row */
    private function map(array $row): Conversation
    {
        return new Conversation(
            (int) $row['id'],
            (int) $row['admin_id'],
            (int) $row['client_id'],
            (string) $row['created_at'],
            (string) $row['updated_at'],
        );
    }
}
