<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Email implements RuleInterface
{
    public function passes(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true; // Required se encarga de esto
        }
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function message(string $field): string
    {
        return "The {$field} field must be a valid email address.";
    }
}