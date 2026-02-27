<?php

declare(strict_types=1);

use Hayabusa\Exceptions\HttpException;
use Hayabusa\Validation\Rules\Email;
use Hayabusa\Validation\Rules\MaxLength;
use Hayabusa\Validation\Rules\MinLength;
use Hayabusa\Validation\Rules\Required;
use Hayabusa\Validation\ValidationResult;
use Hayabusa\Validation\Validator;

// ─── ValidationResult ────────────────────────────────────────────────────────

test('ValidationResult passes when no errors', function () {
    $result = new ValidationResult([]);
    expect($result->passes())->toBeTrue()
        ->and($result->fails())->toBeFalse();
});

test('ValidationResult fails when errors present', function () {
    $result = new ValidationResult(['name' => ['The name field is required.']]);
    expect($result->fails())->toBeTrue()
        ->and($result->errors())->toHaveKey('name');
});

// ─── Required ────────────────────────────────────────────────────────────────

test('Required fails on null', function () {
    $rule = new Required();
    expect($rule->passes('name', null))->toBeFalse();
});

test('Required fails on empty string', function () {
    $rule = new Required();
    expect($rule->passes('name', ''))->toBeFalse();
});

test('Required passes on value', function () {
    $rule = new Required();
    expect($rule->passes('name', 'John'))->toBeTrue();
});

// ─── Email ───────────────────────────────────────────────────────────────────

test('Email passes on valid email', function () {
    $rule = new Email();
    expect($rule->passes('email', 'john@example.com'))->toBeTrue();
});

test('Email fails on invalid email', function () {
    $rule = new Email();
    expect($rule->passes('email', 'not-an-email'))->toBeFalse();
});

test('Email passes on empty value', function () {
    $rule = new Email();
    expect($rule->passes('email', ''))->toBeTrue();
});

// ─── MinLength ───────────────────────────────────────────────────────────────

test('MinLength passes when value meets minimum', function () {
    $rule = new MinLength(3);
    expect($rule->passes('name', 'John'))->toBeTrue();
});

test('MinLength fails when value is too short', function () {
    $rule = new MinLength(5);
    expect($rule->passes('name', 'Jo'))->toBeFalse();
});

test('MinLength passes on empty value', function () {
    $rule = new MinLength(3);
    expect($rule->passes('name', ''))->toBeTrue();
});

// ─── MaxLength ───────────────────────────────────────────────────────────────

test('MaxLength passes when value is within limit', function () {
    $rule = new MaxLength(10);
    expect($rule->passes('name', 'John'))->toBeTrue();
});

test('MaxLength fails when value exceeds limit', function () {
    $rule = new MaxLength(3);
    expect($rule->passes('name', 'John'))->toBeFalse();
});

test('MaxLength passes on empty value', function () {
    $rule = new MaxLength(3);
    expect($rule->passes('name', ''))->toBeTrue();
});

// ─── Validator::make ─────────────────────────────────────────────────────────

test('Validator::make passes with valid data', function () {
    $result = Validator::make(
        ['name' => 'John', 'email' => 'john@example.com'],
        ['name' => 'required', 'email' => 'required|email']
    );
    expect($result->passes())->toBeTrue();
});

test('Validator::make fails with missing required field', function () {
    $result = Validator::make(
        ['name' => ''],
        ['name' => 'required']
    );
    expect($result->fails())->toBeTrue()
        ->and($result->errors())->toHaveKey('name');
});

test('Validator::make supports pipe syntax with multiple rules', function () {
    $result = Validator::make(
        ['email' => 'not-an-email'],
        ['email' => 'required|email']
    );
    expect($result->fails())->toBeTrue()
        ->and($result->errors())->toHaveKey('email');
});

test('Validator::make supports min and max rules', function () {
    $result = Validator::make(
        ['password' => 'ab'],
        ['password' => 'required|min:6|max:20']
    );
    expect($result->fails())->toBeTrue()
        ->and($result->errors()['password'])->toHaveCount(1);
});

test('Validator::make supports array of rule objects', function () {
    $result = Validator::make(
        ['name' => ''],
        ['name' => [new Required(), new MinLength(3)]]
    );
    expect($result->fails())->toBeTrue()
        ->and($result->errors()['name'])->toHaveCount(1);
});

test('Validator::make collects errors for multiple fields', function () {
    $result = Validator::make(
        [],
        ['name' => 'required', 'email' => 'required|email']
    );
    expect($result->errors())->toHaveKeys(['name', 'email']);
});

// ─── Validator::validate ─────────────────────────────────────────────────────

test('Validator::validate throws HttpException 422 on failure', function () {
    expect(fn() => Validator::validate(
        ['name' => ''],
        ['name' => 'required']
    ))->toThrow(HttpException::class);
});

test('Validator::validate exception has status 422', function () {
    try {
        Validator::validate(['name' => ''], ['name' => 'required']);
    } catch (HttpException $e) {
        expect($e->statusCode())->toBe(422)
            ->and($e->errors())->toHaveKey('name');
    }
});

test('Validator::validate passes silently on valid data', function () {
    expect(fn() => Validator::validate(
        ['name' => 'John'],
        ['name' => 'required']
    ))->not->toThrow(HttpException::class);
});

test('Validator throws on unknown rule', function () {
    expect(fn() => Validator::make(
        ['name' => 'John'],
        ['name' => 'unknown_rule']
    ))->toThrow(\InvalidArgumentException::class);
});