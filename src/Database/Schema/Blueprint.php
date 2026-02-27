<?php

declare(strict_types=1);

namespace Hayabusa\Database\Schema;

class Blueprint
{
    private array $columns = [];
    private array $indexes = [];

    // ─── Primary Key ──────────────────────────────────────────────

    public function id(): static
    {
        $this->columns[] = 'id INTEGER PRIMARY KEY AUTOINCREMENT';
        return $this;
    }

    public function uuid(string $name = 'id'): static
    {
        $this->columns[] = "{$name} VARCHAR(36) PRIMARY KEY";
        return $this;
    }

    // ─── Strings ──────────────────────────────────────────────────

    public function string(string $name, int $length = 255): static
    {
        $this->columns[] = "{$name} VARCHAR({$length})";
        return $this;
    }

    public function char(string $name, int $length = 1): static
    {
        $this->columns[] = "{$name} CHAR({$length})";
        return $this;
    }

    public function text(string $name): static
    {
        $this->columns[] = "{$name} TEXT";
        return $this;
    }

    public function mediumText(string $name): static
    {
        $this->columns[] = "{$name} MEDIUMTEXT";
        return $this;
    }

    public function longText(string $name): static
    {
        $this->columns[] = "{$name} LONGTEXT";
        return $this;
    }

    // ─── Integers ─────────────────────────────────────────────────

    public function integer(string $name): static
    {
        $this->columns[] = "{$name} INTEGER";
        return $this;
    }

    public function tinyInteger(string $name): static
    {
        $this->columns[] = "{$name} TINYINT";
        return $this;
    }

    public function smallInteger(string $name): static
    {
        $this->columns[] = "{$name} SMALLINT";
        return $this;
    }

    public function bigInteger(string $name): static
    {
        $this->columns[] = "{$name} BIGINT";
        return $this;
    }

    public function unsignedInteger(string $name): static
    {
        $this->columns[] = "{$name} INTEGER UNSIGNED";
        return $this;
    }

    public function unsignedBigInteger(string $name): static
    {
        $this->columns[] = "{$name} BIGINT UNSIGNED";
        return $this;
    }

    // ─── Floats / Decimals ────────────────────────────────────────

    public function float(string $name, int $precision = 8, int $scale = 2): static
    {
        $this->columns[] = "{$name} FLOAT({$precision},{$scale})";
        return $this;
    }

    public function double(string $name, int $precision = 15, int $scale = 8): static
    {
        $this->columns[] = "{$name} DOUBLE({$precision},{$scale})";
        return $this;
    }

    public function decimal(string $name, int $precision = 10, int $scale = 2): static
    {
        $this->columns[] = "{$name} DECIMAL({$precision},{$scale})";
        return $this;
    }

    // ─── Booleans ─────────────────────────────────────────────────

    public function boolean(string $name): static
    {
        $this->columns[] = "{$name} TINYINT(1) DEFAULT 0";
        return $this;
    }

    // ─── Dates & Times ────────────────────────────────────────────

    public function date(string $name): static
    {
        $this->columns[] = "{$name} DATE";
        return $this;
    }

    public function time(string $name): static
    {
        $this->columns[] = "{$name} TIME";
        return $this;
    }

    public function dateTime(string $name): static
    {
        $this->columns[] = "{$name} DATETIME";
        return $this;
    }

    public function timestamp(string $name): static
    {
        $this->columns[] = "{$name} TIMESTAMP";
        return $this;
    }

    public function timestamps(): static
    {
        $this->columns[] = 'created_at DATETIME DEFAULT CURRENT_TIMESTAMP';
        $this->columns[] = 'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP';
        return $this;
    }

    public function softDeletes(): static
    {
        $this->columns[] = 'deleted_at DATETIME NULL';
        return $this;
    }

    // ─── Binary / JSON ────────────────────────────────────────────

    public function json(string $name): static
    {
        // SQLite no tiene tipo JSON nativo, se guarda como TEXT
        $this->columns[] = "{$name} TEXT";
        return $this;
    }

    public function binary(string $name): static
    {
        $this->columns[] = "{$name} BLOB";
        return $this;
    }

    // ─── Foreign Keys ─────────────────────────────────────────────

    /**
     * Shorthand: foreignId('user_id') → user_id BIGINT UNSIGNED NOT NULL
     * Usa index() o unique() para agregar constraint.
     */
    public function foreignId(string $name): static
    {
        $this->columns[] = "{$name} INTEGER NOT NULL";
        return $this;
    }

    // ─── Modifiers (fluent, modifican el último column) ───────────

    public function nullable(): static
    {
        $last = array_pop($this->columns);
        $this->columns[] = "{$last} NULL";
        return $this;
    }

    public function notNull(): static
    {
        $last = array_pop($this->columns);
        $this->columns[] = "{$last} NOT NULL";
        return $this;
    }

    public function default(mixed $value): static
    {
        $last = array_pop($this->columns);
        $val = is_string($value) ? "'{$value}'" : (is_bool($value) ? ($value ? 1 : 0) : $value);
        $this->columns[] = "{$last} DEFAULT {$val}";
        return $this;
    }

    public function unsigned(): static
    {
        $last = array_pop($this->columns);
        $this->columns[] = "{$last} UNSIGNED";
        return $this;
    }

    // ─── Indexes ──────────────────────────────────────────────────

    public function unique(string $name, string $table): static
    {
        $this->indexes[] = "CREATE UNIQUE INDEX idx_{$table}_{$name} ON {$table}({$name})";
        return $this;
    }

    public function index(string $name, string $table): static
    {
        $this->indexes[] = "CREATE INDEX idx_{$table}_{$name} ON {$table}({$name})";
        return $this;
    }

    /**
     * Índice compuesto: ->compositeIndex(['tenant_id', 'email'], 'users')
     */
    public function compositeIndex(array $columns, string $table): static
    {
        $cols = implode('_', $columns);
        $sql = implode(', ', $columns);
        $this->indexes[] = "CREATE INDEX idx_{$table}_{$cols} ON {$table}({$sql})";
        return $this;
    }

    // ─── Build ────────────────────────────────────────────────────

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