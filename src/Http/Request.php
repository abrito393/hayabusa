<?php

declare(strict_types=1);

namespace Hayabusa\Http;

class Request
{
    private function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private readonly array $headers,
        private readonly array $params = [],
    ) {
    }

    // ── Factories ───────────────────────────────────────────
    public static function fromGlobals(): self
    {
        return new self(
            method: strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            path: parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
            query: $_GET,
            body: self::parseBody(),
            headers: self::parseHeaders(),
        );
    }

    public static function fake(
        string $method = 'GET',
        string $path = '/',
        array $body = [],
        array $query = [],
        array $headers = [],
    ): self {
        return new self($method, $path, $query, $body, $headers);
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

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function all(): array
    {
        return $this->body;
    }
    public function params(): array
    {
        return $this->params;
    }

    // ── Immutable clone with params ─────────────────────────
    public function withParams(array $params): self
    {
        return new self(
            $this->method,
            $this->path,
            $this->query,
            $this->body,
            $this->headers,
            $params,
        );
    }

    // ── Internal ────────────────────────────────────────────
    private static function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }

        return $_POST;
    }

    private static function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $key = strtolower(str_replace('_', '-', substr($k, 5)));
                $headers[$key] = $v;
            }
        }
        return $headers;
    }
}