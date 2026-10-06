<?php

declare(strict_types=1);

use Team\Shared\Database\Database;
use Team\Shared\Database\Migrator;

$basePath = dirname(__DIR__);

require $basePath . '/bootstrap/autoload.php';

/** @var array{path: string} $config */
$config = require $basePath . '/config/database.php';
$database = new Database($config['path']);
$migrator = new Migrator($database->connection(), $basePath . '/migrations');
$executed = $migrator->migrate();

if ($executed === []) {
    echo "Database is already up to date.\n";
    exit(0);
}

foreach ($executed as $version) {
    echo 'Applied migration: ' . $version . "\n";
}
