<?php

declare(strict_types=1);

use Team\Auth\AuthService;
use Team\Auth\UserRepository;
use Team\Chat\ConversationRepository;
use Team\Chat\MessageController;
use Team\Chat\MessageRepository;
use Team\Shared\Database\Database;
use Team\Shared\Database\Migrator;
use Team\Shared\Http\Request;
use Team\Shared\Http\Router;
use Team\Shared\Security\CsrfToken;
use Team\Shared\Session\Session;

require dirname(__DIR__) . '/bootstrap/autoload.php';

$path = sys_get_temp_dir() . '/team-message-api-test-' . bin2hex(random_bytes(8)) . '.sqlite';

/** @return int */
$insertUser = static function (PDO $connection, string $name, string $role): int {
    $statement = $connection->prepare(
        'INSERT INTO users (name, role, password_hash, activated_at, created_at)
         VALUES (:name, :role, :password_hash, :activated_at, :created_at)',
    );
    $statement->execute([
        'name' => $name,
        'role' => $role,
        'password_hash' => password_hash('test-password', PASSWORD_DEFAULT),
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

/**
 * @return array{Router, CsrfToken}
 */
$createApi = static function (PDO $connection, Session $session): array {
    $auth = new AuthService(new UserRepository($connection), $session);
    $csrf = new CsrfToken($session);
    $controller = new MessageController(
        $auth,
        new ConversationRepository($connection),
        new MessageRepository($connection),
        $csrf,
    );
    $router = new Router();
    $router->get('/api/conversations/{id}/messages', [$controller, 'index']);
    $router->post('/api/conversations/{id}/messages', [$controller, 'store']);

    return [$router, $csrf];
};

try {
    $connection = (new Database($path))->connection();
    (new Migrator($connection, dirname(__DIR__) . '/migrations'))->migrate();

    $adminId = $insertUser($connection, 'Мастер', 'admin');
    $clientId = $insertUser($connection, 'Клиент', 'client');
    $otherClientId = $insertUser($connection, 'Другой клиент', 'client');
    $conversationId = $insertConversation($connection, $adminId, $clientId);
    $otherConversationId = $insertConversation($connection, $adminId, $otherClientId);

    $clientSession = new Session('team_client_' . bin2hex(random_bytes(8)));
    $clientSession->start();
    [$clientRouter, $clientCsrf] = $createApi($connection, $clientSession);

    $unauthorized = $clientRouter->dispatch(
        new Request('GET', '/api/conversations/' . $conversationId . '/messages'),
    );
    assert($unauthorized->status() === 401);

    $clientSession->put('user_id', $clientId);
    $token = $clientCsrf->value();

    $invalidCsrf = $clientRouter->dispatch(new Request(
        'POST',
        '/api/conversations/' . $conversationId . '/messages',
        body: ['_csrf' => 'invalid', 'body' => 'Здравствуйте!'],
    ));
    assert($invalidCsrf->status() === 419);

    $created = $clientRouter->dispatch(new Request(
        'POST',
        '/api/conversations/' . $conversationId . '/messages',
        body: ['_csrf' => $token, 'body' => 'Здравствуйте!'],
    ));
    assert($created->status() === 201);
    $createdData = json_decode($created->body(), true, flags: JSON_THROW_ON_ERROR);
    assert($createdData['message']['senderId'] === $clientId);
    assert($createdData['message']['body'] === 'Здравствуйте!');
    $firstMessageId = $createdData['message']['id'];

    $history = $clientRouter->dispatch(
        new Request('GET', '/api/conversations/' . $conversationId . '/messages'),
    );
    $historyData = json_decode($history->body(), true, flags: JSON_THROW_ON_ERROR);
    assert($history->status() === 200);
    assert(count($historyData['messages']) === 1);

    $foreignConversation = $clientRouter->dispatch(
        new Request('GET', '/api/conversations/' . $otherConversationId . '/messages'),
    );
    assert($foreignConversation->status() === 404);

    $clientSession->destroy();

    $adminSession = new Session('team_admin_' . bin2hex(random_bytes(8)));
    $adminSession->start();
    $adminSession->put('user_id', $adminId);
    [$adminRouter, $adminCsrf] = $createApi($connection, $adminSession);

    $reply = $adminRouter->dispatch(new Request(
        'POST',
        '/api/conversations/' . $conversationId . '/messages',
        body: ['_csrf' => $adminCsrf->value(), 'body' => 'Добрый день!'],
    ));
    assert($reply->status() === 201);

    $polling = $adminRouter->dispatch(new Request(
        'GET',
        '/api/conversations/' . $conversationId . '/messages',
        query: ['after_id' => (string) $firstMessageId],
    ));
    $pollingData = json_decode($polling->body(), true, flags: JSON_THROW_ON_ERROR);
    assert($polling->status() === 200);
    assert(count($pollingData['messages']) === 1);
    assert($pollingData['messages'][0]['senderId'] === $adminId);
    assert($pollingData['messages'][0]['body'] === 'Добрый день!');

    $conversations = new ConversationRepository($connection);
    assert($conversations->findForClient($clientId)?->id === $conversationId);
    assert(count($conversations->findForAdmin($adminId)) === 2);

    $adminSession->destroy();

    echo "Message API test passed\n";
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    if (is_file($path)) {
        unlink($path);
    }
}
