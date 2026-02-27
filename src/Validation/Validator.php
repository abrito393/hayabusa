<?php

declare(strict_types=1);

namespace Hayabusa\Validation;

use Hayabusa\Exceptions\HttpException;
use Hayabusa\Validation\Rules\RuleInterface;
use Hayabusa\Validation\Rules\Required;
use Hayabusa\Validation\Rules\Email;
use Hayabusa\Validation\Rules\MinLength;
use Hayabusa\Validation\Rules\MaxLength;
use Hayabusa\Validation\Rules\Integer;
use Hayabusa\Validation\Rules\Numeric;
use Hayabusa\Validation\Rules\Regex;
use Hayabusa\Validation\Rules\In;
use Hayabusa\Validation\Rules\NotIn;
use Hayabusa\Validation\Rules\Between;
use Hayabusa\Validation\Rules\Url;
use Hayabusa\Validation\Rules\Ip;
use Hayabusa\Validation\Rules\Uuid;
use Hayabusa\Validation\Rules\Date;
use Hayabusa\Validation\Rules\Accepted;
use Hayabusa\Validation\Rules\Alpha;
use Hayabusa\Validation\Rules\AlphaNumeric;
use Hayabusa\Validation\Rules\Slug;
use Hayabusa\Validation\Rules\Json;

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

        // ── Reglas con parámetros ────────────────────────────────

        if (str_starts_with($rule, 'min:')) {
            return new MinLength((int) substr($rule, 4));
        }

        if (str_starts_with($rule, 'max:')) {
            return new MaxLength((int) substr($rule, 4));
        }

        if (str_starts_with($rule, 'between:')) {
            [$min, $max] = explode(',', substr($rule, 8), 2);
            return new Between((float) trim($min), (float) trim($max));
        }

        if (str_starts_with($rule, 'in:')) {
            $values = array_map('trim', explode(',', substr($rule, 3)));
            return new In(...$values);
        }

        if (str_starts_with($rule, 'not_in:')) {
            $values = array_map('trim', explode(',', substr($rule, 7)));
            return new NotIn(...$values);
        }

        if (str_starts_with($rule, 'date:')) {
            return new Date(substr($rule, 5));
        }

        if (str_starts_with($rule, 'regex:')) {
            return new Regex(substr($rule, 6));
        }

        // ── Reglas simples ───────────────────────────────────────

        return match ($rule) {
            'required' => new Required(),
            'email' => new Email(),
            'integer' => new Integer(),
            'numeric' => new Numeric(),
            'url' => new Url(),
            'ip' => new Ip(),
            'uuid' => new Uuid(),
            'date' => new Date(),
            'accepted' => new Accepted(),
            'alpha' => new Alpha(),
            'alpha_num' => new AlphaNumeric(),
            'slug' => new Slug(),
            'json' => new Json(),
            default => throw new \InvalidArgumentException("Unknown rule: {$rule}"),
        };
    }
}