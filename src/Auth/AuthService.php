<?php

declare(strict_types=1);

namespace Team\Auth;

use Team\Shared\Session\Session;

final class AuthService
{
    private const USER_ID_KEY = 'user_id';

    public function __construct(
        private readonly UserRepository $users,
        private readonly Session $session,
    ) {
    }

    public function attempt(string $password): ?User
    {
        // TODO(production): не перебирать хэши всех пользователей.
        // Добавить идентификатор приглашения и находить одну запись перед password_verify().
        foreach ($this->users->activeUsers() as $user) {
            if (!password_verify($password, $user->passwordHash)) {
                continue;
            }

            $this->session->regenerate();
            $this->session->put(self::USER_ID_KEY, $user->id);

            return $user;
        }

        return null;
    }

    public function user(): ?User
    {
        $userId = $this->session->get(self::USER_ID_KEY);

        if (!is_int($userId)) {
            return null;
        }

        return $this->users->findById($userId);
    }

    public function logout(): void
    {
        $this->session->destroy();
    }
}
