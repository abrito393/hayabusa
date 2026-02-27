<?php

declare(strict_types=1);

use Hayabusa\Application;
use Hayabusa\Database\Connection;
use Hayabusa\Database\DatabaseManager;

beforeEach(function () {
    DatabaseManager::getInstance()->flush();
});

it('configures database via withDatabase', function () {
    $app = Application::create();
    $app->withDatabase(['driver' => 'sqlite', 'dbname' => ':memory:']);

    $conn = DatabaseManager::getInstance()->connection();
    expect($conn)->toBeInstanceOf(Connection::class);
});

it('withDatabase returns same application instance', function () {
    $app = Application::create();
    $result = $app->withDatabase(['driver' => 'sqlite', 'dbname' => ':memory:']);
    expect($result)->toBe($app);
});

it('withDatabase supports named connections', function () {
    $app = Application::create();
    $app->withDatabase(['driver' => 'sqlite', 'dbname' => ':memory:'], 'secondary');

    $conn = DatabaseManager::getInstance()->connection('secondary');
    expect($conn)->toBeInstanceOf(Connection::class);
});