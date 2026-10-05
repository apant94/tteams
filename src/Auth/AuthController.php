<?php

declare(strict_types=1);

namespace Team\Auth;

use Team\Shared\Http\Request;
use Team\Shared\Http\Response;
use Team\Shared\View\TemplateRenderer;

final class AuthController
{
    public function __construct(
        private readonly TemplateRenderer $templates,
    ) {
    }

    public function showLogin(Request $request): Response
    {
        return Response::html(
            $this->templates->render('auth/login.php'),
        );
    }
}
