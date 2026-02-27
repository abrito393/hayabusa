<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Numeric implements RuleInterface
{
    public function passes(string $field, mixed $value): bool
    {
        return is_numeric($value);
    }

    public function message(string $field): string
    {
        return "The {$field} must be numeric.";
    }
}