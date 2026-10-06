<?php

declare(strict_types=1);

namespace Team\Chat;

use Team\Auth\AuthService;
use Team\Auth\User;
use Team\Shared\Http\Request;
use Team\Shared\Http\Response;
use Team\Shared\Security\CsrfToken;

final class MessageController
{
    private const MAX_MESSAGE_LENGTH = 10_000;

    public function __construct(
        private readonly AuthService $auth,
        private readonly ConversationRepository $conversations,
        private readonly MessageRepository $messages,
        private readonly CsrfToken $csrf,
    ) {
    }

    /** @param array<string, string> $parameters */
    public function index(Request $request, array $parameters): Response
    {
        $user = $this->auth->user();

        if (!$user instanceof User) {
            return $this->error('Требуется авторизация.', 401);
        }

        $conversation = $this->accessibleConversation($parameters, $user);

        if (!$conversation instanceof Conversation) {
            return $this->error('Диалог не найден.', 404);
        }

        $afterId = $request->query('after_id', '0');

        if (!is_string($afterId) || !ctype_digit($afterId)) {
            return $this->error('Некорректный идентификатор последнего сообщения.', 422);
        }

        $messages = $this->messages->findForConversation($conversation->id, (int) $afterId);

        return Response::json([
            'messages' => array_map($this->serialize(...), $messages),
        ]);
    }

    /** @param array<string, string> $parameters */
    public function store(Request $request, array $parameters): Response
    {
        $user = $this->auth->user();

        if (!$user instanceof User) {
            return $this->error('Требуется авторизация.', 401);
        }

        if (!$this->csrf->isValid($request->input('_csrf'))) {
            return $this->error('Сессия формы истекла.', 419);
        }

        $conversation = $this->accessibleConversation($parameters, $user);

        if (!$conversation instanceof Conversation) {
            return $this->error('Диалог не найден.', 404);
        }

        $body = $request->input('body');

        if (!is_string($body) || trim($body) === '') {
            return $this->error('Введите текст сообщения.', 422);
        }

        $body = trim($body);

        if (strlen($body) > self::MAX_MESSAGE_LENGTH) {
            return $this->error('Сообщение слишком длинное.', 422);
        }

        $message = $this->messages->create($conversation->id, $user->id, $body);

        return Response::json(['message' => $this->serialize($message)], 201);
    }

    /**
     * @param array<string, string> $parameters
     */
    private function accessibleConversation(array $parameters, User $user): ?Conversation
    {
        $id = $parameters['id'] ?? null;

        if (!is_string($id) || !ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        $conversation = $this->conversations->findById((int) $id);

        if (!$conversation instanceof Conversation || !$conversation->hasParticipant($user->id)) {
            return null;
        }

        return $conversation;
    }

    /** @return array<string, int|string|null> */
    private function serialize(Message $message): array
    {
        return [
            'id' => $message->id,
            'conversationId' => $message->conversationId,
            'senderId' => $message->senderId,
            'body' => $message->body,
            'createdAt' => $message->createdAt,
            'readAt' => $message->readAt,
        ];
    }

    private function error(string $message, int $status): Response
    {
        return Response::json(['error' => $message], $status);
    }
}
