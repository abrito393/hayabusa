<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Uuid implements RuleInterface
{
    private const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    public function passes(string $field, mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return (bool) preg_match(self::PATTERN, $value);
    }

    public function message(string $field): string
    {
        return "The {$field} must be a valid UUID.";
    }
}