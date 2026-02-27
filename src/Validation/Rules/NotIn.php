<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class NotIn implements RuleInterface
{
    private array $forbidden;

    public function __construct(mixed ...$forbidden)
    {
        $this->forbidden = $forbidden;
    }

    public function passes(string $field, mixed $value): bool
    {
        return !in_array($value, $this->forbidden, strict: true);
    }

    public function message(string $field): string
    {
        $list = implode(', ', $this->forbidden);
        return "The {$field} must not be one of: {$list}.";
    }
}