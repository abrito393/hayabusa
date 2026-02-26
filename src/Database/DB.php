<?php

declare(strict_types=1);

namespace Hayabusa\Database;

class DB
{
    public static function table(string $table): QueryBuilder
    {
        $connection = DatabaseManager::getInstance()->connection();
        return (new QueryBuilder($connection))->table($table);
    }
}