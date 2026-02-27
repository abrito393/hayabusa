<?php

declare(strict_types=1);

namespace Hayabusa\Contexts\Users\Infrastructure\Migrations;

use Hayabusa\Database\Schema\Blueprint;
use Hayabusa\Database\Schema\Migration;
use Hayabusa\Database\Schema\Schema;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $blueprint) {
            $blueprint
                ->id()
                ->string('name')
                ->string('email')
                ->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('users');
    }
}