<?php

declare(strict_types=1);

namespace Hayabusa\Http;

class Response
{
    private array $headers = ['Content-Type' => 'application/json'];

    private function __construct(
        private readonly mixed $data,
        private readonly int $status = 200,
    ) {
    }

    // ── Factories ───────────────────────────────────────────
    public static function json(mixed $data, int $status = 200): self
    {
        return new self($data, $status);
    }

    public static function noContent(): self
    {
        return new self(null, 204);
    }

    public static function notFound(string $message = 'Not found'): self
    {
        return new self(['message' => $message], 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self(['message' => $message], 401);
    }

    public static function unprocessable(array $errors): self
    {
        return new self(['message' => 'Validation failed', 'errors' => $errors], 422);
    }

    // ── Headers ─────────────────────────────────────────────
    public function withHeader(string $key, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$key] = $value;
        return $clone;
    }

    // ── Send ────────────────────────────────────────────────
    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $k => $v) {
            header("{$k}: {$v}");
        }

        if ($this->data !== null) {
            echo json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    // ── Getters ─────────────────────────────────────────────
    public function status(): int
    {
        return $this->status;
    }
    public function data(): mixed
    {
        return $this->data;
    }
}