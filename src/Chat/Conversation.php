<?php

declare(strict_types=1);

namespace Team\Chat;

final readonly class Conversation
{
    public function __construct(
        public int $id,
        public int $adminId,
        public int $clientId,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public function hasParticipant(int $userId): bool
    {
        return $this->adminId === $userId || $this->clientId === $userId;
    }
}
