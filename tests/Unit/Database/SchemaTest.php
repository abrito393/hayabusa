<?php

use Hayabusa\Database\DatabaseManager;
use Hayabusa\Database\Schema\Blueprint;
use Hayabusa\Database\Schema\Schema;

beforeEach(function () {
    DatabaseManager::reset();
    DatabaseManager::getInstance()->addConfig('default', [
        'driver' => 'sqlite',
        'dbname' => ':memory:',
    ]);
});

test('schema create builds table from blueprint', function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('body');
        $table->timestamps();
    });

    expect(Schema::hasTable('posts'))->toBeTrue();
});

test('schema drop removes table', function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
    });

    Schema::drop('posts');

    expect(Schema::hasTable('posts'))->toBeFalse();
});

test('hasTable returns false for non existent table', function () {
    expect(Schema::hasTable('ghost'))->toBeFalse();
});

test('blueprint generates correct sql', function () {
    $blueprint = new Blueprint();
    $blueprint->id()->string('name')->integer('age')->timestamps();

    $sql = $blueprint->toSql('users');

    expect($sql)->toContain('CREATE TABLE users')
        ->and($sql)->toContain('id INTEGER PRIMARY KEY AUTOINCREMENT')
        ->and($sql)->toContain('name VARCHAR(255)')
        ->and($sql)->toContain('age INTEGER')
        ->and($sql)->toContain('created_at DATETIME');
});

test('can insert into table created by schema', function () {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->boolean('active');
        $table->timestamps();
    });

    $conn = DatabaseManager::getInstance()->connection();
    $conn->insert('INSERT INTO users (name, email, active) VALUES (?, ?, ?)', ['Alice', 'alice@test.com', 1]);

    $rows = $conn->query('SELECT * FROM users');
    expect($rows)->toHaveCount(1)
        ->and($rows[0]['name'])->toBe('Alice');
});

test('unique index is created', function () {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('email');
        $table->unique('email', 'users');
    });

    $conn = DatabaseManager::getInstance()->connection();
    $conn->insert('INSERT INTO users (email) VALUES (?)', ['alice@test.com']);

    expect(fn() => $conn->insert('INSERT INTO users (email) VALUES (?)', ['alice@test.com']))
        ->toThrow(\Hayabusa\Database\ConnectionException::class);
});