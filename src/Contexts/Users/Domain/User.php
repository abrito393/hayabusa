<?php

declare(strict_types=1);

namespace Hayabusa\Contexts\Users\Domain;

class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $createdAt,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            id: (int) $data['id'],
            name: $data['name'],
            email: $data['email'],
            createdAt: $data['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt,
        ];
    }
}