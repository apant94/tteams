<?php

declare(strict_types=1);

use Team\Shared\Http\Router;
use Team\Shared\Session\Session;
use Team\Shared\View\TemplateRenderer;

$basePath = dirname(__DIR__);

$session = new Session('team_session');
$session->start();

$router = new Router();
$templates = new TemplateRenderer($basePath . '/templates');
$registerRoutes = require $basePath . '/config/routes.php';
$registerRoutes($router, $templates);

return $router;
