<?php

declare(strict_types=1);

namespace Hayabusa\Console\Commands;

use Hayabusa\Database\MigrationRunner;

class RollbackCommand implements CommandInterface
{
    public function __construct(
        private readonly MigrationRunner $runner,
        private readonly array $migrations = [],
    ) {
    }

    public function signature(): string
    {
        return 'migrate:rollback';
    }

    public function description(): string
    {
        return 'Rollback the last batch of migrations.';
    }

    public function handle(array $args): int
    {
        $rolled = $this->runner->rollback($this->migrations);

        if (empty($rolled)) {
            echo "Nothing to rollback." . PHP_EOL;
            return 0;
        }

        foreach ($rolled as $name) {
            echo "Rolled back: {$name}" . PHP_EOL;
        }

        return 0;
    }
}