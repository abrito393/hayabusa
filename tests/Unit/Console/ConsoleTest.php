<?php

declare(strict_types=1);

use Hayabusa\Console\Commands\MigrateCommand;
use Hayabusa\Console\Commands\RollbackCommand;
use Hayabusa\Console\Kernel;
use Hayabusa\Database\Connection;
use Hayabusa\Database\DatabaseManager;
use Hayabusa\Database\MigrationRunner;
use Hayabusa\Database\Schema\Migration;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function makeConsoleRunner(): MigrationRunner
{
    DatabaseManager::getInstance()->flush();
    DatabaseManager::getInstance()->addConfig('default', [
        'driver' => 'sqlite',
        'dbname' => ':memory:',
    ]);
    $conn = DatabaseManager::getInstance()->connection();
    return new MigrationRunner($conn);
}

function makeConsoleMigration(string &$log, string $name): Migration
{
    $conn = DatabaseManager::getInstance()->connection();
    return new class ($conn, $log, $name) extends Migration {
        public function __construct(
        Connection $conn,
        private string &$log,
        private string $name,
        ) {
            parent::__construct($conn);
        }

        public function up(): void
        {
            $this->log .= "up:{$this->name};"; }
        public function down(): void
        {
            $this->log .= "down:{$this->name};"; }
    };
}

// ─── Kernel ──────────────────────────────────────────────────────────────────

test('Kernel returns 0 with no command (help)', function () {
    $kernel = new Kernel();
    $exit = $kernel->handle(['hayabusa']);
    expect($exit)->toBe(0);
});

test('Kernel returns 0 with --help', function () {
    $kernel = new Kernel();
    $exit = $kernel->handle(['hayabusa', '--help']);
    expect($exit)->toBe(0);
});

test('Kernel returns 1 for unknown command', function () {
    $kernel = new Kernel();
    $exit = $kernel->handle(['hayabusa', 'unknown:command']);
    expect($exit)->toBe(1);
});

test('Kernel registers and resolves commands', function () {
    $runner = makeConsoleRunner();
    $kernel = new Kernel();
    $cmd = new MigrateCommand($runner);
    $kernel->register($cmd);

    expect($kernel->commands())->toHaveKey('migrate');
});

test('Kernel dispatches registered command', function () {
    $runner = makeConsoleRunner();
    $kernel = new Kernel();
    $kernel->register(new MigrateCommand($runner));

    $exit = $kernel->handle(['hayabusa', 'migrate']);
    expect($exit)->toBe(0);
});

// ─── MigrateCommand ──────────────────────────────────────────────────────────

test('MigrateCommand runs pending migrations', function () {
    $log = '';
    $runner = makeConsoleRunner();
    $migrations = ['create_test_table' => makeConsoleMigration($log, 'test')];

    $cmd = new MigrateCommand($runner, $migrations);
    $exit = $cmd->handle([]);

    expect($exit)->toBe(0)
        ->and($log)->toBe('up:test;');
});

test('MigrateCommand outputs nothing to migrate when all ran', function () {
    $log = '';
    $runner = makeConsoleRunner();
    $migrations = ['create_test_table' => makeConsoleMigration($log, 'test')];

    $cmd = new MigrateCommand($runner, $migrations);
    $cmd->handle([]);

    ob_start();
    $cmd->handle([]);
    $output = ob_get_clean();

    expect($output)->toContain('Nothing to migrate.');
});

test('MigrateCommand has correct signature and description', function () {
    $cmd = new MigrateCommand(makeConsoleRunner());
    expect($cmd->signature())->toBe('migrate')
        ->and($cmd->description())->not->toBeEmpty();
});

// ─── RollbackCommand ─────────────────────────────────────────────────────────

test('RollbackCommand rolls back ran migrations', function () {
    $log = '';
    $runner = makeConsoleRunner();
    $migrations = ['create_test_table' => makeConsoleMigration($log, 'test')];

    $runner->run($migrations);
    $log = '';

    $cmd = new RollbackCommand($runner, $migrations);
    $exit = $cmd->handle([]);

    expect($exit)->toBe(0)
        ->and($log)->toBe('down:test;');
});

test('RollbackCommand outputs nothing to rollback when none ran', function () {
    $runner = makeConsoleRunner();
    $cmd = new RollbackCommand($runner);

    ob_start();
    $exit = $cmd->handle([]);
    $output = ob_get_clean();

    expect($exit)->toBe(0)
        ->and($output)->toContain('Nothing to rollback.');
});

test('RollbackCommand has correct signature and description', function () {
    $cmd = new RollbackCommand(makeConsoleRunner());
    expect($cmd->signature())->toBe('migrate:rollback')
        ->and($cmd->description())->not->toBeEmpty();
});