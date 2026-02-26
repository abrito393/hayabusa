<?php

declare(strict_types=1);

use Hayabusa\Database\Connection;
use Hayabusa\Database\MigrationRunner;
use Hayabusa\Database\Schema\Migration;

function makeSQLiteConnection(): Connection
{
    return new Connection(['driver' => 'sqlite', 'dbname' => ':memory:']);
}

function makeRunner(Connection $conn): MigrationRunner
{
    return new MigrationRunner($conn);
}

// --- Migration stubs ---

function createUsersUpMigration(Connection $conn): Migration
{
    return new class ($conn) extends Migration {
        public function up(): void
        {
            $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
        }
        public function down(): void
        {
            $this->connection->execute('DROP TABLE IF EXISTS users');
        }
    };
}

function createPostsUpMigration(Connection $conn): Migration
{
    return new class ($conn) extends Migration {
        public function up(): void
        {
            $this->connection->execute('CREATE TABLE posts (id INTEGER PRIMARY KEY, title TEXT NOT NULL)');
        }
        public function down(): void
        {
            $this->connection->execute('DROP TABLE IF EXISTS posts');
        }
    };
}

// --- Tests ---

it('crea tabla migrations al correr por primera vez', function () {
    $conn = makeSQLiteConnection();
    $runner = makeRunner($conn);

    $runner->run([]);

    $rows = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'");
    expect($rows)->toHaveCount(1);
});

it('ejecuta migraciones pendientes y las registra', function () {
    $conn = makeSQLiteConnection();
    $runner = makeRunner($conn);

    $runner->run([
        '2024_01_create_users' => createUsersUpMigration($conn),
    ]);

    expect($runner->getRan())->toContain('2024_01_create_users');

    $rows = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    expect($rows)->toHaveCount(1);
});

it('no vuelve a ejecutar migraciones ya registradas', function () {
    $conn = makeSQLiteConnection();
    $runner = makeRunner($conn);

    $migration = createUsersUpMigration($conn);

    $runner->run(['2024_01_create_users' => $migration]);
    $runner->run(['2024_01_create_users' => $migration]); // segunda vez — no debe fallar

    expect($runner->getRan())->toHaveCount(1);
});

it('ejecuta múltiples migraciones en orden', function () {
    $conn = makeSQLiteConnection();
    $runner = makeRunner($conn);

    $runner->run([
        '2024_01_create_users' => createUsersUpMigration($conn),
        '2024_02_create_posts' => createPostsUpMigration($conn),
    ]);

    expect($runner->getRan())->toHaveCount(2);
});

it('rollback elimina tablas y borra registros', function () {
    $conn = makeSQLiteConnection();
    $runner = makeRunner($conn);

    $migrations = [
        '2024_01_create_users' => createUsersUpMigration($conn),
        '2024_02_create_posts' => createPostsUpMigration($conn),
    ];

    $runner->run($migrations);
    expect($runner->getRan())->toHaveCount(2);

    $runner->rollback($migrations);
    expect($runner->getRan())->toHaveCount(0);
});

it('rollback solo revierte las que fueron ejecutadas', function () {
    $conn = makeSQLiteConnection();
    $runner = makeRunner($conn);

    $migrations = [
        '2024_01_create_users' => createUsersUpMigration($conn),
        '2024_02_create_posts' => createPostsUpMigration($conn),
    ];

    $runner->run(['2024_01_create_users' => $migrations['2024_01_create_users']]);
    $runner->rollback($migrations); // posts nunca corrió — no debe explotar

    expect($runner->getRan())->toHaveCount(0);
});

it('reset vuelve a ejecutar todas las migraciones', function () {
    $conn = makeSQLiteConnection();
    $runner = makeRunner($conn);

    $migrations = [
        '2024_01_create_users' => createUsersUpMigration($conn),
    ];

    $runner->run($migrations);
    $runner->reset($migrations);

    expect($runner->getRan())->toContain('2024_01_create_users');
});