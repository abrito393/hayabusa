<?php

declare(strict_types=1);

namespace Hayabusa\Validation;

use Hayabusa\Exceptions\HttpException;
use Hayabusa\Validation\Rules\RuleInterface;
use Hayabusa\Validation\Rules\Required;
use Hayabusa\Validation\Rules\Email;
use Hayabusa\Validation\Rules\MinLength;
use Hayabusa\Validation\Rules\MaxLength;

class Validator
{
    private function __construct(
        private readonly array $data,
        private readonly array $rules
    ) {
    }

    public static function make(array $data, array $rules): ValidationResult
    {
        return (new static($data, $rules))->run();
    }

    public static function validate(array $data, array $rules): void
    {
        $result = static::make($data, $rules);

        if ($result->fails()) {
            throw new HttpException(422, 'Validation failed', $result->errors());
        }
    }

    private function run(): ValidationResult
    {
        $errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;

            foreach ($this->parseRules($fieldRules) as $rule) {
                if (!$rule->passes($field, $value)) {
                    $errors[$field][] = $rule->message($field);
                }
            }
        }

        return new ValidationResult($errors);
    }

    private function parseRules(array|string $rules): array
    {
        if (is_array($rules)) {
            return array_map(fn($rule) => $this->resolveRule($rule), $rules);
        }

        return array_map(
            fn($rule) => $this->resolveRule(trim($rule)),
            explode('|', $rules)
        );
    }

    private function resolveRule(RuleInterface|string $rule): RuleInterface
    {
        if ($rule instanceof RuleInterface) {
            return $rule;
        }

        if (str_starts_with($rule, 'min:')) {
            return new MinLength((int) substr($rule, 4));
        }

        if (str_starts_with($rule, 'max:')) {
            return new MaxLength((int) substr($rule, 4));
        }

        return match ($rule) {
            'required' => new Required(),
            'email' => new Email(),
            default => throw new \InvalidArgumentException("Unknown rule: {$rule}"),
        };
    }
}