<?php

declare(strict_types=1);

use Team\Shared\Database\Database;

$basePath = dirname(__DIR__);

require $basePath . '/bootstrap/autoload.php';

/** @var array{path: string} $config */
$config = require $basePath . '/config/database.php';
$database = new Database($config['path']);
$connection = $database->connection();
$now = gmdate('Y-m-d\TH:i:s\Z');

$users = [
    [
        'id' => 1,
        'name' => 'Мастер',
        'role' => 'admin',
        'password' => getenv('TEAM_DEMO_ADMIN_PASSWORD') ?: 'admin-demo',
    ],
    [
        'id' => 2,
        'name' => 'Тестовый клиент',
        'role' => 'client',
        'password' => getenv('TEAM_DEMO_CLIENT_PASSWORD') ?: 'client-demo',
    ],
];

$statement = $connection->prepare(
    'INSERT INTO users (id, name, role, password_hash, activated_at, created_at)
     VALUES (:id, :name, :role, :password_hash, :activated_at, :created_at)
     ON CONFLICT(id) DO UPDATE SET
        name = excluded.name,
        role = excluded.role,
        password_hash = excluded.password_hash,
        activated_at = excluded.activated_at',
);

foreach ($users as $user) {
    $statement->execute([
        'id' => $user['id'],
        'name' => $user['name'],
        'role' => $user['role'],
        'password_hash' => password_hash($user['password'], PASSWORD_DEFAULT),
        'activated_at' => $now,
        'created_at' => $now,
    ]);
}

$conversation = $connection->prepare(
    'INSERT INTO conversations (admin_id, client_id, created_at, updated_at)
     VALUES (:admin_id, :client_id, :created_at, :updated_at)
     ON CONFLICT(client_id) DO UPDATE SET admin_id = excluded.admin_id',
);
$conversation->execute([
    'admin_id' => 1,
    'client_id' => 2,
    'created_at' => $now,
    'updated_at' => $now,
]);

echo "Demo users created. These credentials are for local development only.\n";
echo "Demo conversation created.\n";
echo 'Admin password: ' . $users[0]['password'] . "\n";
echo 'Client password: ' . $users[1]['password'] . "\n";
