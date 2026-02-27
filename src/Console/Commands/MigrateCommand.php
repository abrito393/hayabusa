<?php

declare(strict_types=1);

namespace Hayabusa\Console\Commands;

use Hayabusa\Database\MigrationRunner;

class MigrateCommand implements CommandInterface
{
    public function __construct(
        private readonly MigrationRunner $runner,
        private readonly array $migrations = [],
    ) {
    }

    public function signature(): string
    {
        return 'migrate';
    }

    public function description(): string
    {
        return 'Run all pending migrations.';
    }

    public function handle(array $args): int
    {
        $ran = $this->runner->run($this->migrations);

        if (empty($ran)) {
            echo "Nothing to migrate." . PHP_EOL;
            return 0;
        }

        foreach ($ran as $name) {
            echo "Migrated: {$name}" . PHP_EOL;
        }

        return 0;
    }
}