<?php

declare(strict_types=1);

namespace Hayabusa\Http;

class Router
{
    private array $routes = [];
    private array $groupStack = [];

    // ── HTTP verbs ──────────────────────────────────────────
    public function get(string $path, mixed $handler): Route
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): Route
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, mixed $handler): Route
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, mixed $handler): Route
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, mixed $handler): Route
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    // ── Group ───────────────────────────────────────────────
    public function group(string $prefix, callable $callback): void
    {
        $this->groupStack[] = $prefix;
        $callback($this);
        array_pop($this->groupStack);
    }

    // ── Dispatch ────────────────────────────────────────────
    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            $params = [];
            if ($route->matches($request->method(), $request->path(), $params)) {
                return $route->run($request->withParams($params));
            }
        }

        return Response::json(['error' => 'Route not found'], 404);
    }

    // ── Internal ────────────────────────────────────────────
    private function addRoute(string $method, string $path, mixed $handler): Route
    {
        $prefix = implode('', $this->groupStack);
        $route = new Route($method, $prefix . $path, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function routes(): array
    {
        return $this->routes;
    }
}