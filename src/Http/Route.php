<?php

declare(strict_types=1);

namespace Hayabusa\Http;

class Route
{
    private array $middlewares = [];

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly mixed $handler,
    ) {
    }

    // ── Middleware ──────────────────────────────────────────
    public function middleware(string|array $middleware): self
    {
        $this->middlewares = array_merge(
            $this->middlewares,
            (array) $middleware
        );
        return $this;
    }

    // ── Match ───────────────────────────────────────────────
    public function matches(string $method, string $path, array &$params = []): bool
    {
        if ($this->method !== $method) {
            return false;
        }

        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $this->path);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $path, $matches)) {
            return false;
        }

        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        return true;
    }

    // ── Run ─────────────────────────────────────────────────
    public function run(Request $request): Response
    {
        $handler = $this->handler;

        // Closure
        if (is_callable($handler)) {
            return $handler($request);
        }

        // [Controller::class, 'method']
        if (is_array($handler)) {
            [$class, $method] = $handler;
            return (new $class())->$method($request);
        }

        throw new \RuntimeException("Invalid route handler");
    }

    // ── Getters ─────────────────────────────────────────────
    public function method(): string
    {
        return $this->method;
    }
    public function path(): string
    {
        return $this->path;
    }
    public function middlewares(): array
    {
        return $this->middlewares;
    }
}