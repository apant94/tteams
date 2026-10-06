<?php

declare(strict_types=1);

use Team\Auth\AuthService;
use Team\Auth\UserRepository;
use Team\Shared\Database\Database;
use Team\Shared\Database\Migrator;
use Team\Shared\Session\Session;

require dirname(__DIR__) . '/bootstrap/autoload.php';

$path = sys_get_temp_dir() . '/team-auth-test-' . bin2hex(random_bytes(8)) . '.sqlite';
$session = new Session('team_test_' . bin2hex(random_bytes(8)));

try {
    $connection = (new Database($path))->connection();
    (new Migrator($connection, dirname(__DIR__) . '/migrations'))->migrate();

    $statement = $connection->prepare(
        'INSERT INTO users (name, role, password_hash, activated_at, created_at)
         VALUES (:name, :role, :password_hash, :activated_at, :created_at)',
    );
    $statement->execute([
        'name' => 'Мастер',
        'role' => 'admin',
        'password_hash' => password_hash('secret-admin', PASSWORD_DEFAULT),
        'activated_at' => '2026-01-01T00:00:00Z',
        'created_at' => '2026-01-01T00:00:00Z',
    ]);

    $session->start();
    $auth = new AuthService(new UserRepository($connection), $session);

    assert($auth->attempt('wrong-password') === null);
    assert($auth->user() === null);

    $user = $auth->attempt('secret-admin');

    assert($user !== null);
    assert($user->name === 'Мастер');
    assert($user->isAdmin());
    assert($auth->user()?->id === $user->id);

    $auth->logout();

    echo "Auth service test passed\n";
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    if (is_file($path)) {
        unlink($path);
    }
}
