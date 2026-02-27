<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Slug implements RuleInterface
{
    public function passes(string $field, mixed $value): bool
    {
        return is_string($value) && (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value);
    }

    public function message(string $field): string
    {
        return "The {$field} must be a valid slug (lowercase letters, numbers, and hyphens).";
    }
}