<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Required implements RuleInterface
{
    public function passes(string $field, mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    public function message(string $field): string
    {
        return "The {$field} field is required.";
    }
}