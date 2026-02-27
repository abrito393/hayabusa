<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Regex implements RuleInterface
{
    public function __construct(private readonly string $pattern)
    {
    }

    public function passes(string $field, mixed $value): bool
    {
        if (!is_string($value) && !is_numeric($value)) {
            return false;
        }
        return (bool) preg_match($this->pattern, (string) $value);
    }

    public function message(string $field): string
    {
        return "The {$field} format is invalid.";
    }
}