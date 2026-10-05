<?php

declare(strict_types=1);

namespace Team\Shared\Http;

final class Request
{
    /**
     * @param array<string, string> $query
     * @param array<string, mixed> $body
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        return new self(
            $method,
            is_string($path) && $path !== '' ? $path : '/',
            self::stringValues($_GET),
            $_POST,
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, ?string $default = null): ?string
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * @param array<mixed> $values
     * @return array<string, string>
     */
    private static function stringValues(array $values): array
    {
        $result = [];

        foreach ($values as $key => $value) {
            if (is_string($key) && is_scalar($value)) {
                $result[$key] = (string) $value;
            }
        }

        return $result;
    }
}
