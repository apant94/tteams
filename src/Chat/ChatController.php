<?php

declare(strict_types=1);

namespace Team\Chat;

use Team\Auth\AuthService;
use Team\Auth\User;
use Team\Auth\UserRepository;
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
        private readonly ConversationRepository $conversations,
        private readonly UserRepository $users,
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

        $conversation = $this->conversations->findForClient($user->id);
        $admin = $conversation instanceof Conversation
            ? $this->users->findById($conversation->adminId)
            : null;

        return Response::html($this->templates->render('client/chat.php', [
            'user' => $user,
            'csrfToken' => $this->csrf->value(),
            'conversation' => $conversation,
            'peerName' => $admin?->name ?? 'Мастер',
        ]));
    }

    /** @param array<string, string> $parameters */
    public function admin(Request $request, array $parameters = []): Response
    {
        $user = $this->auth->user();

        if (!$user instanceof User) {
            return Response::redirect('/');
        }

        if (!$user->isAdmin()) {
            return Response::redirect('/chat');
        }

        $summaries = $this->conversations->findSummariesForAdmin($user->id);
        $requestedId = $parameters['id'] ?? null;

        if ($requestedId !== null && (!ctype_digit($requestedId) || (int) $requestedId < 1)) {
            return Response::html('<h1>Диалог не найден</h1>', 404);
        }

        $selectedId = $requestedId !== null ? (int) $requestedId : ($summaries[0]->id ?? null);
        $conversation = is_int($selectedId)
            ? $this->conversations->findById($selectedId)
            : null;

        if (
            $requestedId !== null
            && (!$conversation instanceof Conversation || $conversation->adminId !== $user->id)
        ) {
            return Response::html('<h1>Диалог не найден</h1>', 404);
        }

        $selectedSummary = null;

        foreach ($summaries as $summary) {
            if ($conversation instanceof Conversation && $summary->id === $conversation->id) {
                $selectedSummary = $summary;
                break;
            }
        }

        return Response::html($this->templates->render('master/chats.php', [
            'user' => $user,
            'csrfToken' => $this->csrf->value(),
            'conversations' => $summaries,
            'conversation' => $conversation,
            'peerName' => $selectedSummary?->clientName ?? 'Клиент',
        ]));
    }
}
