<?php

declare(strict_types=1);

namespace Team\Chat;

final readonly class ConversationSummary
{
    public function __construct(
        public int $id,
        public int $clientId,
        public string $clientName,
        public ?string $lastMessage,
        public string $updatedAt,
    ) {
    }
}
