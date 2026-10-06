<?php

declare(strict_types=1);

use Team\Shared\Http\Router;
use Team\Shared\Database\Database;
use Team\Shared\Session\Session;
use Team\Shared\View\TemplateRenderer;

$basePath = dirname(__DIR__);

$session = new Session('team_session');
$session->start();

$router = new Router();
$templates = new TemplateRenderer($basePath . '/templates');
$databaseConfig = require $basePath . '/config/database.php';
$database = new Database($databaseConfig['path']);
$registerRoutes = require $basePath . '/config/routes.php';
$registerRoutes($router, $templates, $session, $database->connection());

return $router;
