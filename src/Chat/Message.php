<?php

declare(strict_types=1);

namespace Team\Chat;

final readonly class Message
{
    public function __construct(
        public int $id,
        public int $conversationId,
        public int $senderId,
        public string $body,
        public string $createdAt,
        public ?string $readAt,
    ) {
    }
}
