<?php

declare(strict_types=1);

namespace Team\Chat;

use Team\Auth\AuthService;
use Team\Auth\User;
use Team\Shared\Http\Request;
use Team\Shared\Http\Response;
use Team\Shared\Security\CsrfToken;
use Team\Shared\View\TemplateRenderer;

final class ChatController
{
    public function __construct(
        private readonly TemplateRenderer $templates,
        private readonly AuthService $auth,
        private readonly CsrfToken $csrf,
    ) {
    }

    public function client(Request $request): Response
    {
        $user = $this->auth->user();

        if (!$user instanceof User) {
            return Response::redirect('/');
        }

        if ($user->isAdmin()) {
            return Response::redirect('/admin/chats');
        }

        return Response::html($this->templates->render('client/chat.php', [
            'user' => $user,
            'csrfToken' => $this->csrf->value(),
        ]));
    }

    public function admin(Request $request): Response
    {
        $user = $this->auth->user();

        if (!$user instanceof User) {
            return Response::redirect('/');
        }

        if (!$user->isAdmin()) {
            return Response::redirect('/chat');
        }

        return Response::html($this->templates->render('master/chats.php', [
            'user' => $user,
            'csrfToken' => $this->csrf->value(),
        ]));
    }
}
