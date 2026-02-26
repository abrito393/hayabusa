<?php

declare(strict_types=1);

namespace Hayabusa\Database;

class DatabaseManager
{
    private static self $instance;
    /** @var array<string, Connection> */
    private array $connections = [];
    private array $configs = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addConfig(string $name, array $config): void
    {
        $this->configs[$name] = $config;
    }

    public function connection(string $name = 'default'): Connection
    {
        if (!isset($this->connections[$name])) {
            if (!isset($this->configs[$name])) {
                throw new ConnectionException("No config found for connection: {$name}");
            }
            $this->connections[$name] = new Connection($this->configs[$name]);
        }
        return $this->connections[$name];
    }

    /** Useful for testing — forces a new connection */
    public function reconnect(string $name = 'default'): Connection
    {
        unset($this->connections[$name]);
        return $this->connection($name);
    }

    /** Reset all — used in tests */
    public function flush(): void
    {
        $this->connections = [];
        $this->configs = [];
    }

    /** Reset singleton — used in tests */
    public static function reset(): void
    {
        static::$instance = new self();
    }
}
