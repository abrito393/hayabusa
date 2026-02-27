<?php

declare(strict_types=1);

namespace Hayabusa\Validation;

class ValidationResult
{
    public function __construct(
        private readonly array $errors = []
    ) {
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }
}