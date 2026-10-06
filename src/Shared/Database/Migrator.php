<?php

declare(strict_types=1);

namespace Team\Shared\Database;

use PDO;
use RuntimeException;

final class Migrator
{
    public function __construct(
        private readonly PDO $connection,
        private readonly string $migrationsPath,
    ) {
    }

    /** @return list<string> */
    public function migrate(): array
    {
        if (!is_dir($this->migrationsPath)) {
            throw new RuntimeException('Migrations directory does not exist: ' . $this->migrationsPath);
        }

        $this->createMigrationsTable();
        $applied = $this->appliedVersions();
        $files = glob($this->migrationsPath . '/*.sql');

        if ($files === false) {
            throw new RuntimeException('Unable to read migrations directory.');
        }

        sort($files, SORT_STRING);
        $executed = [];

        foreach ($files as $file) {
            $version = basename($file);

            if (isset($applied[$version])) {
                continue;
            }

            $sql = file_get_contents($file);

            if ($sql === false) {
                throw new RuntimeException('Unable to read migration: ' . $version);
            }

            $this->connection->beginTransaction();

            try {
                $this->connection->exec($sql);

                $statement = $this->connection->prepare(
                    'INSERT INTO schema_migrations (version, applied_at) VALUES (:version, :applied_at)',
                );
                $statement->execute([
                    'version' => $version,
                    'applied_at' => gmdate('Y-m-d\TH:i:s\Z'),
                ]);

                $this->connection->commit();
                $executed[] = $version;
            } catch (\Throwable $exception) {
                if ($this->connection->inTransaction()) {
                    $this->connection->rollBack();
                }

                throw $exception;
            }
        }

        return $executed;
    }

    private function createMigrationsTable(): void
    {
        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                version TEXT PRIMARY KEY,
                applied_at TEXT NOT NULL
            )',
        );
    }

    /** @return array<string, true> */
    private function appliedVersions(): array
    {
        $versions = $this->connection
            ->query('SELECT version FROM schema_migrations')
            ->fetchAll(PDO::FETCH_COLUMN);

        $result = [];

        foreach ($versions as $version) {
            if (is_string($version)) {
                $result[$version] = true;
            }
        }

        return $result;
    }
}
