<?php

declare(strict_types=1);

use Team\Auth\AuthController;
use Team\Auth\AuthService;
use Team\Auth\UserRepository;
use Team\Chat\ChatController;
use Team\Shared\Http\Router;
use Team\Shared\Security\CsrfToken;
use Team\Shared\Session\Session;
use Team\Shared\View\TemplateRenderer;

return static function (
    Router $router,
    TemplateRenderer $templates,
    Session $session,
    \PDO $connection,
): void {
    $users = new UserRepository($connection);
    $auth = new AuthService($users, $session);
    $csrf = new CsrfToken($session);
    $authController = new AuthController($templates, $auth, $csrf);
    $chatController = new ChatController($templates, $auth, $csrf);

    $router->get('/', [$authController, 'showLogin']);
    $router->post('/login', [$authController, 'login']);
    $router->post('/logout', [$authController, 'logout']);
    $router->get('/chat', [$chatController, 'client']);
    $router->get('/admin/chats', [$chatController, 'admin']);
};
