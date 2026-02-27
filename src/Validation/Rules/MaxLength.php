<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class MaxLength implements RuleInterface
{
    public function __construct(private readonly int $max)
    {
    }

    public function passes(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        return mb_strlen((string) $value) <= $this->max;
    }

    public function message(string $field): string
    {
        return "The {$field} field must not exceed {$this->max} characters.";
    }
}