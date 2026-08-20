<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $currentGroupMiddlewares = [];
    private string $currentGroupPrefix = '';

    public function get(string $path, array|callable $handler, array $middlewares = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, array|callable $handler, array $middlewares = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, array|callable $handler, array $middlewares = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->currentGroupPrefix;
        $previousMiddlewares = $this->currentGroupMiddlewares;

        if (isset($attributes['prefix'])) {
            $this->currentGroupPrefix = $previousPrefix . '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $middlewares = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
            $this->currentGroupMiddlewares = array_merge($previousMiddlewares, $middlewares);
        }

        $callback($this);

        $this->currentGroupPrefix = $previousPrefix;
        $this->currentGroupMiddlewares = $previousMiddlewares;
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares = []): self
    {
        $fullPath = $this->currentGroupPrefix . '/' . trim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');
        $allMiddlewares = array_merge($this->currentGroupMiddlewares, $middlewares);

        // Convert {param} to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $fullPath);
        $regex = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'regex' => $regex,
            'handler' => $handler,
            'middlewares' => $allMiddlewares
        ];

        return $this;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        $allowedMethods = [];
        $matchedRoute = null;
        $params = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                $allowedMethods[] = $route['method'];
                if ($route['method'] === $method) {
                    $matchedRoute = $route;
                    // extract named captures
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $params[$key] = urldecode($value);
                        }
                    }
                    break;
                }
            }
        }

        if ($matchedRoute === null) {
            if (!empty($allowedMethods)) {
                $res = new Response();
                return $res->setStatusCode(405)
                    ->header('Allow', implode(', ', array_unique($allowedMethods)))
                    ->setContent('405 Method Not Allowed');
            }

            if ($request->isAjax() || str_starts_with($path, '/api/')) {
                return (new Response())->json([
                    'success' => false,
                    'error' => 'Endpoint não encontrado (404)'
                ], 404);
            }

            $res = new Response();
            return $res->setStatusCode(404)->setContent(
                View::render('errors.404', ['path' => $path], 'public')
            );
        }

        // Execute Middlewares Pipeline
        foreach ($matchedRoute['middlewares'] as $middleware) {
            $middlewareInstance = is_string($middleware) ? new $middleware() : $middleware;
            $result = $middlewareInstance->handle($request);
            if ($result instanceof Response) {
                return $result;
            }
        }

        $handler = $matchedRoute['handler'];
        $positionalParams = array_values($params);

        if (is_callable($handler)) {
            $result = call_user_func_array($handler, array_merge([$request], $positionalParams));
        } elseif (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $action] = $handler;
            $controller = new $controllerClass();
            $result = call_user_func_array([$controller, $action], array_merge([$request], $positionalParams));
        } else {
            throw new \RuntimeException('Handler de rota inválido.');
        }

        if ($result instanceof Response) {
            return $result;
        }

        $response = new Response();
        if (is_array($result) || is_object($result)) {
            return $response->json($result);
        }

        return $response->setContent((string)$result);
    }
}
