<?php

declare(strict_types=1);

namespace Hayabusa\Console\Commands;

interface CommandInterface
{
    public function signature(): string;
    public function description(): string;
    public function handle(array $args): int;
}