<?php

declare(strict_types=1);

namespace Hayabusa\Database;

class QueryBuilder
{
    private string $table;
    private array $wheres = [];
    private array $bindings = [];
    private ?int $limitValue = null;
    private ?string $orderByColumn = null;
    private string $orderByDirection = 'ASC';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function where(string $column, mixed $value): static
    {
        $this->wheres[] = "{$column} = ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limitValue = $limit;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orderByColumn = $column;
        $this->orderByDirection = strtoupper($direction);
        return $this;
    }

    public function get(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $sql .= $this->buildWhere();
        $sql .= $this->buildOrderBy();
        $sql .= $this->buildLimit();

        return $this->connection->query($sql, $this->bindings);
    }

    public function first(): ?array
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as aggregate FROM {$this->table}";
        $sql .= $this->buildWhere();

        $result = $this->connection->query($sql, $this->bindings);
        return (int) ($result[0]['aggregate'] ?? 0);
    }

    public function insert(array $data): string|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        return $this->connection->insert($sql, array_values($data));
    }

    public function update(array $data): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET {$set}";
        $sql .= $this->buildWhere();

        $bindings = array_merge(array_values($data), $this->bindings);

        return $this->connection->execute($sql, $bindings);
    }

    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";
        $sql .= $this->buildWhere();

        return $this->connection->execute($sql, $this->bindings);
    }

    private function buildWhere(): string
    {
        if (empty($this->wheres)) {
            return '';
        }
        return ' WHERE ' . implode(' AND ', $this->wheres);
    }

    private function buildOrderBy(): string
    {
        if ($this->orderByColumn === null) {
            return '';
        }
        return " ORDER BY {$this->orderByColumn} {$this->orderByDirection}";
    }

    private function buildLimit(): string
    {
        if ($this->limitValue === null) {
            return '';
        }
        return " LIMIT {$this->limitValue}";
    }
}