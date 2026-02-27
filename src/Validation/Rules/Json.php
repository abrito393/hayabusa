<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Json implements RuleInterface
{
    public function passes(string $field, mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function message(string $field): string
    {
        return "The {$field} must be a valid JSON string.";
    }
}