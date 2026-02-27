<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Accepted implements RuleInterface
{
    private const TRUTHY = ['yes', 'on', '1', 'true', true, 1];

    public function passes(string $field, mixed $value): bool
    {
        return in_array($value, self::TRUTHY, strict: true);
    }

    public function message(string $field): string
    {
        return "The {$field} must be accepted.";
    }
}