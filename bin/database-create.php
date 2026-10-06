<?php

declare(strict_types=1);

use Team\Shared\Database\Database;

$basePath = dirname(__DIR__);

require $basePath . '/bootstrap/autoload.php';

/** @var array{path: string} $config */
$config = require $basePath . '/config/database.php';
$database = new Database($config['path']);
$connection = $database->connection();
$version = $connection->query('SELECT sqlite_version()')->fetchColumn();

echo sprintf(
    "SQLite database created: %s\nSQLite version: %s\n",
    $config['path'],
    is_string($version) ? $version : 'unknown',
);
