<?php

declare(strict_types=1);

use Team\Auth\AuthService;
use Team\Auth\UserRepository;
use Team\Chat\ChatController;
use Team\Chat\ConversationRepository;
use Team\Shared\Database\Database;
use Team\Shared\Database\Migrator;
use Team\Shared\Http\Request;
use Team\Shared\Security\CsrfToken;
use Team\Shared\Session\Session;
use Team\Shared\View\TemplateRenderer;

require dirname(__DIR__) . '/bootstrap/autoload.php';

$basePath = dirname(__DIR__);
$path = sys_get_temp_dir() . '/team-chat-controller-test-' . bin2hex(random_bytes(8)) . '.sqlite';

/** @return int */
$insertUser = static function (PDO $connection, string $name, string $role): int {
    $statement = $connection->prepare(
        'INSERT INTO users (name, role, password_hash, activated_at, created_at)
         VALUES (:name, :role, :password_hash, :activated_at, :created_at)',
    );
    $statement->execute([
        'name' => $name,
        'role' => $role,
        'password_hash' => 'hash',
        'activated_at' => '2026-01-01T00:00:00Z',
        'created_at' => '2026-01-01T00:00:00Z',
    ]);

    return (int) $connection->lastInsertId();
};

/** @return int */
$insertConversation = static function (PDO $connection, int $adminId, int $clientId): int {
    $statement = $connection->prepare(
        'INSERT INTO conversations (admin_id, client_id, created_at, updated_at)
         VALUES (:admin_id, :client_id, :created_at, :updated_at)',
    );
    $statement->execute([
        'admin_id' => $adminId,
        'client_id' => $clientId,
        'created_at' => '2026-01-01T00:00:00Z',
        'updated_at' => '2026-01-01T00:00:00Z',
    ]);

    return (int) $connection->lastInsertId();
};

/** @return ChatController */
$createController = static function (PDO $connection, Session $session) use ($basePath): ChatController {
    $users = new UserRepository($connection);

    return new ChatController(
        new TemplateRenderer($basePath . '/templates'),
        new AuthService($users, $session),
        new CsrfToken($session),
        new ConversationRepository($connection),
        $users,
    );
};

try {
    $connection = (new Database($path))->connection();
    (new Migrator($connection, $basePath . '/migrations'))->migrate();

    $adminId = $insertUser($connection, 'Мастер', 'admin');
    $clientId = $insertUser($connection, 'Анна', 'client');
    $conversationId = $insertConversation($connection, $adminId, $clientId);
    $otherAdminId = $insertUser($connection, 'Другой мастер', 'admin');
    $otherClientId = $insertUser($connection, 'Чужой клиент', 'client');
    $otherConversationId = $insertConversation($connection, $otherAdminId, $otherClientId);

    $clientSession = new Session('team_chat_client_' . bin2hex(random_bytes(8)));
    $clientSession->start();
    $clientSession->put('user_id', $clientId);
    $clientPage = $createController($connection, $clientSession)->client(new Request('GET', '/chat'));

    assert($clientPage->status() === 200);
    assert(str_contains($clientPage->body(), 'data-conversation-id="' . $conversationId . '"'));
    assert(str_contains($clientPage->body(), 'data-current-user-id="' . $clientId . '"'));
    assert(str_contains($clientPage->body(), 'Мастер'));
    assert(str_contains($clientPage->body(), '/assets/chat.js'));

    $clientSession->destroy();

    $adminSession = new Session('team_chat_admin_' . bin2hex(random_bytes(8)));
    $adminSession->start();
    $adminSession->put('user_id', $adminId);
    $adminController = $createController($connection, $adminSession);
    $adminPage = $adminController->admin(new Request('GET', '/admin/chats'));

    assert($adminPage->status() === 200);
    assert(str_contains($adminPage->body(), 'Анна'));
    assert(str_contains($adminPage->body(), '/admin/chats/' . $conversationId));
    assert(str_contains($adminPage->body(), 'conversation-card--selected'));

    $foreignPage = $adminController->admin(
        new Request('GET', '/admin/chats/' . $otherConversationId),
        ['id' => (string) $otherConversationId],
    );
    assert($foreignPage->status() === 404);

    $adminSession->destroy();

    echo "Chat controller test passed\n";
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    if (is_file($path)) {
        unlink($path);
    }
}
