<?php

declare(strict_types=1);

namespace Hayabusa\Console;

use Hayabusa\Console\Commands\CommandInterface;

class Kernel
{
    private array $commands = [];

    public function register(CommandInterface $command): static
    {
        $this->commands[$command->signature()] = $command;
        return $this;
    }

    public function handle(array $argv): int
    {
        $signature = $argv[1] ?? null;
        $args = array_slice($argv, 2);

        if ($signature === null || $signature === '--help') {
            $this->printHelp();
            return 0;
        }

        if (!isset($this->commands[$signature])) {
            echo "Unknown command: {$signature}" . PHP_EOL;
            echo "Run 'php hayabusa --help' to see available commands." . PHP_EOL;
            return 1;
        }

        return $this->commands[$signature]->handle($args);
    }

    public function commands(): array
    {
        return $this->commands;
    }

    private function printHelp(): void
    {
        echo "Hayabusa Framework CLI" . PHP_EOL;
        echo PHP_EOL;
        echo "Available commands:" . PHP_EOL;

        foreach ($this->commands as $command) {
            echo sprintf("  %-20s %s", $command->signature(), $command->description()) . PHP_EOL;
        }
    }
}