<?php

declare(strict_types=1);

use Team\Shared\Database\Database;
use Team\Shared\Database\Migrator;

require dirname(__DIR__) . '/bootstrap/autoload.php';

$path = sys_get_temp_dir() . '/team-migrator-test-' . bin2hex(random_bytes(8)) . '.sqlite';

try {
    $connection = (new Database($path))->connection();
    $migrator = new Migrator($connection, dirname(__DIR__) . '/migrations');

    assert($migrator->migrate() === ['001_create_users.sql']);
    assert($migrator->migrate() === []);

    $tables = $connection
        ->query("SELECT name FROM sqlite_master WHERE type = 'table'")
        ->fetchAll(PDO::FETCH_COLUMN);

    assert(in_array('schema_migrations', $tables, true));
    assert(in_array('users', $tables, true));

    echo "Migrator test passed\n";
} finally {
    if (is_file($path)) {
        unlink($path);
    }
}
