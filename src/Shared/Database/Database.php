<?php

declare(strict_types=1);

namespace Team\Shared\Database;

use PDO;
use RuntimeException;

final class Database
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly string $path,
    ) {
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $directory = dirname($this->path);

        if (!is_dir($directory)) {
            throw new RuntimeException('Database directory does not exist: ' . $directory);
        }

        if (!is_writable($directory)) {
            throw new RuntimeException('Database directory is not writable: ' . $directory);
        }

        $this->connection = new PDO('sqlite:' . $this->path, options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $this->connection->exec('PRAGMA foreign_keys = ON');
        $this->connection->exec('PRAGMA busy_timeout = 5000');

        return $this->connection;
    }
}
