<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Date implements RuleInterface
{
    public function __construct(private readonly string $format = 'Y-m-d')
    {
    }

    public function passes(string $field, mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        $d = \DateTime::createFromFormat($this->format, $value);
        return $d !== false && $d->format($this->format) === $value;
    }

    public function message(string $field): string
    {
        return "The {$field} must be a valid date ({$this->format}).";
    }
}