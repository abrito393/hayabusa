<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Ip implements RuleInterface
{
    public function passes(string $field, mixed $value): bool
    {
        return filter_var($value, FILTER_passes_IP) !== false;
    }

    public function message(string $field): string
    {
        return "The {$field} must be a valid IP address.";
    }
}