<?php

declare(strict_types=1);

namespace Hayabusa\Validation\Rules;

class Confirmed implements RuleInterface
{
    /**
     * Valida que exista un campo {field}_confirmation con el mismo valor.
     * Se pasa el array completo de data via setData() antes de validar.
     */
    private array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function passes(string $field, mixed $value): bool
    {
        $confirmationKey = $field . '_confirmation';
        return isset($this->data[$confirmationKey])
            && $this->data[$confirmationKey] === $value;
    }

    public function message(string $field): string
    {
        return "The {$field} confirmation does not match.";
    }
}