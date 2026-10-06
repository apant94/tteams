<?php

declare(strict_types=1);

use Team\Shared\Database\Database;

require dirname(__DIR__) . '/bootstrap/autoload.php';

$path = sys_get_temp_dir() . '/team-database-test-' . bin2hex(random_bytes(8)) . '.sqlite';

try {
    $database = new Database($path);
    $connection = $database->connection();

    assert(is_file($path));
    assert($connection === $database->connection());
    assert((int) $connection->query('PRAGMA foreign_keys')->fetchColumn() === 1);
    assert((int) $connection->query('PRAGMA busy_timeout')->fetchColumn() === 5000);

    echo "Database test passed\n";
} finally {
    if (is_file($path)) {
        unlink($path);
    }
}
