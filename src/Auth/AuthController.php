<?php

declare(strict_types=1);

namespace Team\Auth;

use Team\Shared\Http\Request;
use Team\Shared\Http\Response;
use Team\Shared\Security\CsrfToken;
use Team\Shared\View\TemplateRenderer;

final class AuthController
{
    public function __construct(
        private readonly TemplateRenderer $templates,
        private readonly AuthService $auth,
        private readonly CsrfToken $csrf,
    ) {
    }

    public function showLogin(Request $request): Response
    {
        $user = $this->auth->user();

        if ($user instanceof User) {
            return $this->redirectFor($user);
        }

        return $this->loginPage();
    }

    public function login(Request $request): Response
    {
        if (!$this->csrf->isValid($request->input('_csrf'))) {
            return $this->loginPage('Сессия формы истекла. Обновите страницу и попробуйте снова.', 419);
        }

        $password = $request->input('password');

        if (!is_string($password) || $password === '') {
            return $this->loginPage('Введите пароль.', 422);
        }

        $user = $this->auth->attempt($password);

        if (!$user instanceof User) {
            return $this->loginPage('Неверный пароль.', 422);
        }

        return $this->redirectFor($user);
    }

    public function logout(Request $request): Response
    {
        if (!$this->csrf->isValid($request->input('_csrf'))) {
            return Response::html('<h1>Сессия формы истекла</h1>', 419);
        }

        $this->auth->logout();

        return Response::redirect('/');
    }

    private function loginPage(?string $error = null, int $status = 200): Response
    {
        return Response::html(
            $this->templates->render('auth/login.php', [
                'csrfToken' => $this->csrf->value(),
                'error' => $error,
            ]),
            $status,
        );
    }

    private function redirectFor(User $user): Response
    {
        return Response::redirect($user->isAdmin() ? '/admin/chats' : '/chat');
    }
}
