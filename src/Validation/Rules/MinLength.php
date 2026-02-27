<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class MinLength implements RuleInterface
{
    public function __construct(private readonly int $min)
    {
    }

    public function passes(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        return mb_strlen((string) $value) >= $this->min;
    }

    public function message(string $field): string
    {
        return "The {$field} field must be at least {$this->min} characters.";
    }
}