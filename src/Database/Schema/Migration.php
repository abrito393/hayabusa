<?php

declare(strict_types=1);

namespace Hayabusa\Database\Schema;

use Hayabusa\Database\Connection;

abstract class Migration
{
    public function __construct(protected readonly Connection $connection)
    {
    }

    abstract public function up(): void;
    abstract public function down(): void;
}