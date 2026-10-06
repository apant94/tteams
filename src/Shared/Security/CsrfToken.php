<?php

declare(strict_types=1);

namespace Team\Shared\Security;

use Team\Shared\Session\Session;

final class CsrfToken
{
    private const SESSION_KEY = '_csrf_token';

    public function __construct(
        private readonly Session $session,
    ) {
    }

    public function value(): string
    {
        $token = $this->session->get(self::SESSION_KEY);

        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $this->session->put(self::SESSION_KEY, $token);

        return $token;
    }

    public function isValid(mixed $token): bool
    {
        $expected = $this->session->get(self::SESSION_KEY);

        return is_string($expected)
            && is_string($token)
            && hash_equals($expected, $token);
    }
}
