<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class In implements RuleInterface
{
    private array $allowed;

    public function __construct(mixed ...$allowed)
    {
        $this->allowed = $allowed;
    }

    public function passes(string $field, mixed $value): bool
    {
        return in_array($value, $this->allowed, strict: true);
    }

    public function message(string $field): string
    {
        $list = implode(', ', $this->allowed);
        return "The {$field} must be one of: {$list}.";
    }
}