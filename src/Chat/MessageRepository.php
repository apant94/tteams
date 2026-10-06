<?php

declare(strict_types=1);

namespace Team\Chat;

use PDO;

final class MessageRepository
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    /** @return list<Message> */
    public function findForConversation(int $conversationId, int $afterId = 0): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, conversation_id, sender_id, body, created_at, read_at
             FROM messages
             WHERE conversation_id = :conversation_id AND id > :after_id
             ORDER BY id',
        );
        $statement->bindValue('conversation_id', $conversationId, PDO::PARAM_INT);
        $statement->bindValue('after_id', $afterId, PDO::PARAM_INT);
        $statement->execute();

        return array_map($this->map(...), $statement->fetchAll());
    }

    public function create(int $conversationId, int $senderId, string $body): Message
    {
        $createdAt = gmdate('Y-m-d\TH:i:s\Z');
        $this->connection->beginTransaction();

        try {
            $statement = $this->connection->prepare(
                'INSERT INTO messages (conversation_id, sender_id, body, created_at)
                 VALUES (:conversation_id, :sender_id, :body, :created_at)',
            );
            $statement->execute([
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'body' => $body,
                'created_at' => $createdAt,
            ]);

            $messageId = (int) $this->connection->lastInsertId();
            $updateConversation = $this->connection->prepare(
                'UPDATE conversations SET updated_at = :updated_at WHERE id = :id',
            );
            $updateConversation->execute([
                'updated_at' => $createdAt,
                'id' => $conversationId,
            ]);

            $this->connection->commit();
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }

        return new Message(
            $messageId,
            $conversationId,
            $senderId,
            $body,
            $createdAt,
            null,
        );
    }

    /** @param array<string, mixed> $row */
    private function map(array $row): Message
    {
        return new Message(
            (int) $row['id'],
            (int) $row['conversation_id'],
            (int) $row['sender_id'],
            (string) $row['body'],
            (string) $row['created_at'],
            is_string($row['read_at']) ? $row['read_at'] : null,
        );
    }
}
