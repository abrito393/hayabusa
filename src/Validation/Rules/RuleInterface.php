<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

interface RuleInterface
{
    public function passes(string $field, mixed $value): bool;
    public function message(string $field): string;
}