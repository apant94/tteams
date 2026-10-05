<?php

declare(strict_types=1);

namespace Team\Shared\Http;

use Closure;

final class Router
{
    /**
     * @var list<array{
     *     method: string,
     *     pattern: string,
     *     parameterNames: list<string>,
     *     handler: callable
     * }>
     */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(Request $request): Response
    {
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $request->path(), $matches)) {
                continue;
            }

            $pathMatched = true;

            if ($route['method'] !== $request->method()) {
                continue;
            }

            $parameters = [];

            foreach ($route['parameterNames'] as $name) {
                $parameters[$name] = $matches[$name];
            }

            $response = ($route['handler'])($request, $parameters);

            if (!$response instanceof Response) {
                throw new \LogicException('Route handlers must return a Response instance.');
            }

            return $response;
        }

        if ($pathMatched) {
            return Response::html('<h1>Метод не поддерживается</h1>', 405);
        }

        return Response::html('<h1>Страница не найдена</h1>', 404);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $parameterNames = [];
        $quotedPath = preg_quote($path, '#');
        $pattern = preg_replace_callback(
            '/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/',
            static function (array $matches) use (&$parameterNames): string {
                $parameterNames[] = $matches[1];

                return '(?P<' . $matches[1] . '>[^/]+)';
            },
            $quotedPath,
        );

        if (!is_string($pattern)) {
            throw new \LogicException('Unable to compile route pattern.');
        }

        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . $pattern . '/?$#',
            'parameterNames' => $parameterNames,
            'handler' => Closure::fromCallable($handler),
        ];
    }
}
