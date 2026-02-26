<?php

use Hayabusa\Database\Connection;
use Hayabusa\Database\ConnectionException;

beforeEach(function () {
    $this->conn = new Connection(['driver' => 'sqlite', 'dbname' => ':memory:']);
    $this->conn->execute('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT)');
});

test('query returns empty array on no rows', function () {
    expect($this->conn->query('SELECT * FROM users'))->toBe([]);
});

test('insert returns last insert id', function () {
    $id = $this->conn->insert('INSERT INTO users (name, email) VALUES (?, ?)', ['Alice', 'alice@test.com']);
    expect($id)->toBe('1');
});

test('query returns inserted rows', function () {
    $this->conn->insert('INSERT INTO users (name, email) VALUES (?, ?)', ['Alice', 'alice@test.com']);
    $rows = $this->conn->query('SELECT * FROM users');
    expect($rows)->toHaveCount(1)
        ->and($rows[0]['name'])->toBe('Alice');
});

test('execute returns affected rows count', function () {
    $this->conn->insert('INSERT INTO users (name, email) VALUES (?, ?)', ['Alice', 'alice@test.com']);
    $affected = $this->conn->execute('UPDATE users SET name = ? WHERE id = ?', ['Bob', 1]);
    expect($affected)->toBe(1);
});

test('query with bindings filters correctly', function () {
    $this->conn->insert('INSERT INTO users (name, email) VALUES (?, ?)', ['Alice', 'alice@test.com']);
    $this->conn->insert('INSERT INTO users (name, email) VALUES (?, ?)', ['Bob', 'bob@test.com']);

    $rows = $this->conn->query('SELECT * FROM users WHERE name = ?', ['Alice']);
    expect($rows)->toHaveCount(1)
        ->and($rows[0]['email'])->toBe('alice@test.com');
});

test('transaction commits on success', function () {
    $this->conn->transaction(function (Connection $conn) {
        $conn->insert('INSERT INTO users (name, email) VALUES (?, ?)', ['Alice', 'alice@test.com']);
    });

    expect($this->conn->query('SELECT * FROM users'))->toHaveCount(1);
});

test('transaction rolls back on exception', function () {
    try {
        $this->conn->transaction(function (Connection $conn) {
            $conn->insert('INSERT INTO users (name, email) VALUES (?, ?)', ['Alice', 'alice@test.com']);
            throw new \RuntimeException('fail');
        });
    } catch (\RuntimeException) {
    }

    expect($this->conn->query('SELECT * FROM users'))->toHaveCount(0);
});

test('throws ConnectionException on invalid driver', function () {
    expect(fn() => new Connection(['driver' => 'oracle', 'dbname' => 'x']))
        ->toThrow(ConnectionException::class);
});

test('throws ConnectionException on bad query', function () {
    expect(fn() => $this->conn->query('SELECT * FROM non_existent_table'))
        ->toThrow(ConnectionException::class);
});