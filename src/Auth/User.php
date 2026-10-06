<?php

declare(strict_types=1);

namespace Team\Auth;

final readonly class User
{
    public function __construct(
        public int $id,
        public string $name,
        public string $role,
        public string $passwordHash,
    ) {
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
