<?php

declare(strict_types=1);

use Hayabusa\Application;
use Hayabusa\Contexts\Users\Infrastructure\Migrations\CreateUsersTable;
use Hayabusa\Database\DatabaseManager;
use Hayabusa\Database\MigrationRunner;
use Hayabusa\Http\Request;

beforeEach(function () {
    DatabaseManager::getInstance()->flush();

    $this->app = Application::create();
    $this->app->withDatabase(['driver' => 'sqlite', 'dbname' => ':memory:']);
    $this->app->loadRoutes(dirname(__DIR__, 4) . '/src/Contexts/Users/Infrastructure/routes.php');

    $conn = DatabaseManager::getInstance()->connection();
    $runner = new MigrationRunner($conn);
    $runner->run([
        'create_users_table' => new CreateUsersTable($conn),
    ]);
});

it('GET /users returns empty array', function () {
    $response = $this->app->handle(Request::fake('GET', '/users'));
    expect($response->getStatusCode())->toBe(200);
    expect($response->data())->toBe([]);
});

it('POST /users creates a user', function () {
    $request = Request::fake('POST', '/users', body: ['name' => 'John', 'email' => 'john@example.com']);
    $response = $this->app->handle($request);

    expect($response->getStatusCode())->toBe(201);
    expect($response->data())->toMatchArray([
        'name' => 'John',
        'email' => 'john@example.com',
    ]);
});

it('GET /users returns created users', function () {
    $this->app->handle(Request::fake('POST', '/users', body: ['name' => 'John', 'email' => 'john@example.com']));
    $this->app->handle(Request::fake('POST', '/users', body: ['name' => 'Jane', 'email' => 'jane@example.com']));

    $response = $this->app->handle(Request::fake('GET', '/users'));
    expect($response->getStatusCode())->toBe(200);
    expect($response->data())->toHaveCount(2);
});