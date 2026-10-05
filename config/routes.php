<?php

declare(strict_types=1);

use Team\Auth\AuthController;
use Team\Shared\Http\Router;
use Team\Shared\View\TemplateRenderer;

return static function (Router $router, TemplateRenderer $templates): void {
    $authController = new AuthController($templates);

    $router->get('/', [$authController, 'showLogin']);
};
