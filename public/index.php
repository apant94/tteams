<?php

declare(strict_types=1);

use Team\Shared\Http\Request;
use Team\Shared\Http\Response;

$basePath = dirname(__DIR__);

require $basePath . '/bootstrap/autoload.php';

$router = require $basePath . '/bootstrap/app.php';

try {
    $response = $router->dispatch(Request::fromGlobals());
} catch (Throwable $exception) {
    error_log($exception->__toString());

    $response = Response::html(
        '<!doctype html><html lang="ru"><meta charset="utf-8"><title>Ошибка</title>'
        . '<body><h1>Не удалось открыть страницу</h1><p>Попробуйте обновить её позже.</p></body></html>',
        500,
    );
}

$response->send();
