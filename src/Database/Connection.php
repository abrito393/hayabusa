<?php

declare(strict_types=1);

namespace Hayabusa\Database;

use PDO;
use PDOException;
use PDOStatement;

class Connection
{
    private PDO $pdo;

    public function __construct(private readonly array $config)
    {
        $this->connect();
    }

    private function connect(): void
    {
        $dsn = $this->buildDsn();

        try {
            $this->pdo = new PDO(
                $dsn,
                $this->config['user'] ?? null,
                $this->config['password'] ?? null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new ConnectionException("Connection failed: {$e->getMessage()}", previous: $e);
        }
    }

    private function buildDsn(): string
    {
        return match ($this->config['driver']) {
            'mysql' => "mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset=utf8mb4",
            'pgsql' => "pgsql:host={$this->config['host']};dbname={$this->config['dbname']}",
            'sqlite' => "sqlite:{$this->config['dbname']}",
            default => throw new ConnectionException("Unsupported driver: {$this->config['driver']}"),
        };
    }

    /**
     * SELECT — returns array of rows
     */
    public function query(string $sql, array $bindings = []): array
    {
        $stmt = $this->prepare($sql, $bindings);
        return $stmt->fetchAll();
    }

    /**
     * INSERT / UPDATE / DELETE — returns affected rows
     */
    public function execute(string $sql, array $bindings = []): int
    {
        $stmt = $this->prepare($sql, $bindings);
        return $stmt->rowCount();
    }

    /**
     * INSERT — returns last inserted id
     */
    public function insert(string $sql, array $bindings = []): string|false
    {
        $this->prepare($sql, $bindings);
        return $this->pdo->lastInsertId();
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    private function prepare(string $sql, array $bindings): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            return $stmt;
        } catch (PDOException $e) {
            throw new ConnectionException("Query failed: {$e->getMessage()}", previous: $e);
        }
    }
}