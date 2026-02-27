<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class AlphaNumeric implements RuleInterface
{
    public function passes(string $field, mixed $value): bool
    {
        return is_string($value) && ctype_alnum($value);
    }

    public function message(string $field): string
    {
        return "The {$field} must contain only letters and numbers.";
    }
}