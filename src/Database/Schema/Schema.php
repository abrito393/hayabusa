<?php

declare(strict_types=1);

namespace Hayabusa\Database\Schema;

use Hayabusa\Database\DatabaseManager;

class Schema
{
    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint();
        $callback($blueprint);

        $connection = DatabaseManager::getInstance()->connection();
        $connection->execute($blueprint->toSql($table));

        foreach ($blueprint->getIndexes() as $indexSql) {
            $connection->execute($indexSql);
        }
    }

    public static function drop(string $table): void
    {
        DatabaseManager::getInstance()->connection()
            ->execute("DROP TABLE IF EXISTS {$table}");
    }

    public static function hasTable(string $table): bool
    {
        $connection = DatabaseManager::getInstance()->connection();
        $result = $connection->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name=?",
            [$table]
        );
        return !empty($result);
    }
}