<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Integer implements RuleInterface
{
    public function passes(string $field, mixed $value): bool
    {
        return filter_var($value, FILTER_passes_INT) !== false;
    }

    public function message(string $field): string
    {
        return "The {$field} must be an integer.";
    }
}