<?php

use Hayabusa\Database\ConnectionException;
use Hayabusa\Database\DatabaseManager;

beforeEach(function () {
    DatabaseManager::reset();
});

$sqliteConfig = ['driver' => 'sqlite', 'dbname' => ':memory:'];

test('returns connection for registered config', function () use ($sqliteConfig) {
    $manager = DatabaseManager::getInstance();
    $manager->addConfig('default', $sqliteConfig);

    expect($manager->connection('default'))->toBeInstanceOf(\Hayabusa\Database\Connection::class);
});

test('returns same connection instance (lazy singleton per name)', function () use ($sqliteConfig) {
    $manager = DatabaseManager::getInstance();
    $manager->addConfig('default', $sqliteConfig);

    expect($manager->connection())->toBe($manager->connection());
});

test('throws if no config registered', function () {
    expect(fn() => DatabaseManager::getInstance()->connection('missing'))
        ->toThrow(ConnectionException::class);
});

test('reconnect returns new connection instance', function () use ($sqliteConfig) {
    $manager = DatabaseManager::getInstance();
    $manager->addConfig('default', $sqliteConfig);

    $first = $manager->connection();
    $second = $manager->reconnect();

    expect($first)->not->toBe($second);
});

test('flush clears all connections and configs', function () use ($sqliteConfig) {
    $manager = DatabaseManager::getInstance();
    $manager->addConfig('default', $sqliteConfig);
    $manager->connection();
    $manager->flush();

    expect(fn() => $manager->connection())
        ->toThrow(ConnectionException::class);
});