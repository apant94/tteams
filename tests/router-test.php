<?php

declare(strict_types=1);

use Team\Shared\Http\Request;
use Team\Shared\Http\Response;
use Team\Shared\Http\Router;

require dirname(__DIR__) . '/bootstrap/autoload.php';

$router = new Router();
$router->get('/clients/{id}', static function (Request $request, array $parameters): Response {
    return Response::json(['clientId' => $parameters['id']]);
});

$success = $router->dispatch(new Request('GET', '/clients/42'));
assert($success->status() === 200);
assert($success->body() === '{"clientId":"42"}');

$wrongMethod = $router->dispatch(new Request('POST', '/clients/42'));
assert($wrongMethod->status() === 405);

$missing = $router->dispatch(new Request('GET', '/missing'));
assert($missing->status() === 404);

echo "Router test passed\n";
