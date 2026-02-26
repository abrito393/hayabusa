<?php

declare(strict_types=1);

namespace Hayabusa\Database\Schema;

class Blueprint
{
    private array $columns = [];
    private array $indexes = [];

    public function id(): static
    {
        $this->columns[] = 'id INTEGER PRIMARY KEY AUTOINCREMENT';
        return $this;
    }

    public function string(string $name, int $length = 255): static
    {
        $this->columns[] = "{$name} VARCHAR({$length})";
        return $this;
    }

    public function text(string $name): static
    {
        $this->columns[] = "{$name} TEXT";
        return $this;
    }

    public function integer(string $name): static
    {
        $this->columns[] = "{$name} INTEGER";
        return $this;
    }

    public function boolean(string $name): static
    {
        $this->columns[] = "{$name} TINYINT(1) DEFAULT 0";
        return $this;
    }

    public function nullable(): static
    {
        $last = array_pop($this->columns);
        $this->columns[] = "{$last} NULL";
        return $this;
    }

    public function default(mixed $value): static
    {
        $last = array_pop($this->columns);
        $val = is_string($value) ? "'{$value}'" : $value;
        $this->columns[] = "{$last} DEFAULT {$val}";
        return $this;
    }

    public function timestamps(): static
    {
        $this->columns[] = 'created_at DATETIME DEFAULT CURRENT_TIMESTAMP';
        $this->columns[] = 'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP';
        return $this;
    }

    public function unique(string $name, string $table): static
    {
        $this->indexes[] = "CREATE UNIQUE INDEX idx_{$table}_{$name} ON {$table}({$name})";
        return $this;
    }

    public function toSql(string $table): string
    {
        $columns = implode(', ', $this->columns);
        return "CREATE TABLE {$table} ({$columns})";
    }

    public function getIndexes(): array
    {
        return $this->indexes;
    }
}