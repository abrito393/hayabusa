<?php

use Hayabusa\Database\Connection;
use Hayabusa\Database\DB;
use Hayabusa\Database\DatabaseManager;
use Hayabusa\Database\QueryBuilder;

beforeEach(function () {
    DatabaseManager::reset();

    $this->conn = new Connection(['driver' => 'sqlite', 'dbname' => ':memory:']);
    $this->conn->execute('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, active INTEGER DEFAULT 1)');

    DatabaseManager::getInstance()->addConfig('default', ['driver' => 'sqlite', 'dbname' => ':memory:']);

    $this->qb = new QueryBuilder($this->conn);
    $this->qb->table('users');
});

test('insert adds a row and returns id', function () {
    $id = $this->qb->insert(['name' => 'Alice', 'email' => 'alice@test.com']);
    expect($id)->toBe('1');
});

test('get returns all rows', function () {
    $this->qb->insert(['name' => 'Alice', 'email' => 'alice@test.com']);
    (new QueryBuilder($this->conn))->table('users')->insert(['name' => 'Bob', 'email' => 'bob@test.com']);

    $rows = (new QueryBuilder($this->conn))->table('users')->get();
    expect($rows)->toHaveCount(2);
});

test('where filters rows', function () {
    $this->qb->insert(['name' => 'Alice', 'email' => 'alice@test.com', 'active' => 1]);
    (new QueryBuilder($this->conn))->table('users')->insert(['name' => 'Bob', 'email' => 'bob@test.com', 'active' => 0]);

    $rows = (new QueryBuilder($this->conn))->table('users')->where('active', 1)->get();
    expect($rows)->toHaveCount(1)
        ->and($rows[0]['name'])->toBe('Alice');
});

test('first returns single row or null', function () {
    $this->qb->insert(['name' => 'Alice', 'email' => 'alice@test.com']);

    $row = (new QueryBuilder($this->conn))->table('users')->first();
    expect($row)->toBeArray()
        ->and($row['name'])->toBe('Alice');

    $none = (new QueryBuilder($this->conn))->table('users')->where('name', 'Ghost')->first();
    expect($none)->toBeNull();
});

test('count returns correct number', function () {
    $this->qb->insert(['name' => 'Alice', 'email' => 'alice@test.com']);
    (new QueryBuilder($this->conn))->table('users')->insert(['name' => 'Bob', 'email' => 'bob@test.com']);

    $count = (new QueryBuilder($this->conn))->table('users')->count();
    expect($count)->toBe(2);
});

test('update modifies row and returns affected count', function () {
    $this->qb->insert(['name' => 'Alice', 'email' => 'alice@test.com']);

    $affected = (new QueryBuilder($this->conn))->table('users')->where('name', 'Alice')->update(['name' => 'Alicia']);
    expect($affected)->toBe(1);

    $row = (new QueryBuilder($this->conn))->table('users')->first();
    expect($row['name'])->toBe('Alicia');
});

test('delete removes row and returns affected count', function () {
    $this->qb->insert(['name' => 'Alice', 'email' => 'alice@test.com']);

    $affected = (new QueryBuilder($this->conn))->table('users')->where('name', 'Alice')->delete();
    expect($affected)->toBe(1);

    expect((new QueryBuilder($this->conn))->table('users')->count())->toBe(0);
});

test('orderBy sorts results', function () {
    $this->qb->insert(['name' => 'Charlie', 'email' => 'c@test.com']);
    (new QueryBuilder($this->conn))->table('users')->insert(['name' => 'Alice', 'email' => 'a@test.com']);

    $rows = (new QueryBuilder($this->conn))->table('users')->orderBy('name')->get();
    expect($rows[0]['name'])->toBe('Alice');
});

test('limit restricts number of results', function () {
    $this->qb->insert(['name' => 'Alice', 'email' => 'a@test.com']);
    (new QueryBuilder($this->conn))->table('users')->insert(['name' => 'Bob', 'email' => 'b@test.com']);

    $rows = (new QueryBuilder($this->conn))->table('users')->limit(1)->get();
    expect($rows)->toHaveCount(1);
});