<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Between implements RuleInterface
{
    public function __construct(
        private readonly int|float $min,
        private readonly int|float $max,
    ) {
    }

    public function passes(string $field, mixed $value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }
        $n = (float) $value;
        return $n >= $this->min && $n <= $this->max;
    }

    public function message(string $field): string
    {
        return "The {$field} must be between {$this->min} and {$this->max}.";
    }
}