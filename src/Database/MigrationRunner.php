<?php

declare(strict_types=1);

namespace Hayabusa\Database;

use Hayabusa\Database\Schema\Migration;

class MigrationRunner
{
    private const TABLE = 'migrations';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function run(array $migrations): void
    {
        $this->ensureMigrationsTable();
        $ran = $this->getRan();

        foreach ($migrations as $name => $migration) {
            if (in_array($name, $ran, true)) {
                continue;
            }
            $migration->up();
            $this->connection->execute(
                'INSERT INTO ' . self::TABLE . ' (migration, ran_at) VALUES (?, ?)',
                [$name, date('Y-m-d H:i:s')]
            );
        }
    }

    public function rollback(array $migrations): void
    {
        $this->ensureMigrationsTable();
        $ran = $this->getRan();

        foreach (array_reverse($migrations, true) as $name => $migration) {
            if (!in_array($name, $ran, true)) {
                continue;
            }
            $migration->down();
            $this->connection->execute(
                'DELETE FROM ' . self::TABLE . ' WHERE migration = ?',
                [$name]
            );
        }
    }

    public function getRan(): array
    {
        $rows = $this->connection->query('SELECT migration FROM ' . self::TABLE);
        return array_column($rows, 'migration');
    }

    public function reset(array $migrations): void
    {
        $this->rollback($migrations);
        $this->run($migrations);
    }

    private function ensureMigrationsTable(): void
    {
        $this->connection->execute('
            CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL UNIQUE,
                ran_at DATETIME NOT NULL
            )
        ');
    }
}