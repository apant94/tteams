<?php

declare(strict_types=1);

use Team\Shared\Database\Database;
use Team\Shared\Database\Migrator;

require dirname(__DIR__) . '/bootstrap/autoload.php';

$path = sys_get_temp_dir() . '/team-migrator-test-' . bin2hex(random_bytes(8)) . '.sqlite';

try {
    $connection = (new Database($path))->connection();
    $migrator = new Migrator($connection, dirname(__DIR__) . '/migrations');

    assert($migrator->migrate() === [
        '001_create_users.sql',
        '002_create_conversations_and_messages.sql',
    ]);
    assert($migrator->migrate() === []);

    $tables = $connection
        ->query("SELECT name FROM sqlite_master WHERE type = 'table'")
        ->fetchAll(PDO::FETCH_COLUMN);

    assert(in_array('schema_migrations', $tables, true));
    assert(in_array('users', $tables, true));
    assert(in_array('conversations', $tables, true));
    assert(in_array('messages', $tables, true));

    $insertUser = $connection->prepare(
        'INSERT INTO users (name, role, password_hash, activated_at, created_at)
         VALUES (:name, :role, :password_hash, :activated_at, :created_at)',
    );
    $insertUser->execute([
        'name' => 'Мастер',
        'role' => 'admin',
        'password_hash' => 'hash',
        'activated_at' => '2026-01-01T00:00:00Z',
        'created_at' => '2026-01-01T00:00:00Z',
    ]);
    $adminId = (int) $connection->lastInsertId();

    $insertUser->execute([
        'name' => 'Клиент',
        'role' => 'client',
        'password_hash' => 'hash',
        'activated_at' => '2026-01-01T00:00:00Z',
        'created_at' => '2026-01-01T00:00:00Z',
    ]);
    $clientId = (int) $connection->lastInsertId();

    $insertConversation = $connection->prepare(
        'INSERT INTO conversations (admin_id, client_id, created_at, updated_at)
         VALUES (:admin_id, :client_id, :created_at, :updated_at)',
    );
    $insertConversation->execute([
        'admin_id' => $adminId,
        'client_id' => $clientId,
        'created_at' => '2026-01-01T00:00:00Z',
        'updated_at' => '2026-01-01T00:00:00Z',
    ]);
    $conversationId = (int) $connection->lastInsertId();

    $insertMessage = $connection->prepare(
        'INSERT INTO messages (conversation_id, sender_id, body, created_at)
         VALUES (:conversation_id, :sender_id, :body, :created_at)',
    );
    $insertMessage->execute([
        'conversation_id' => $conversationId,
        'sender_id' => $clientId,
        'body' => 'Здравствуйте!',
        'created_at' => '2026-01-01T00:01:00Z',
    ]);

    assert($connection->query('SELECT body FROM messages')->fetchColumn() === 'Здравствуйте!');

    $connection->exec('DELETE FROM conversations WHERE id = ' . $conversationId);
    assert((int) $connection->query('SELECT COUNT(*) FROM messages')->fetchColumn() === 0);

    echo "Migrator test passed\n";
} finally {
    if (is_file($path)) {
        unlink($path);
    }
}
