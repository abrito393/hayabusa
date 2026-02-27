<?php

declare(strict_types=1);

namespace Hayabusa\Contexts\Users\Application\UseCases;

use Hayabusa\Contexts\Users\Domain\User;
use Hayabusa\Database\DB;

class CreateUserUseCase
{
    public function execute(string $name, string $email): User
    {
        $id = DB::table('users')->insert([
            'name' => $name,
            'email' => $email,
        ]);

        return User::fromArray(
            DB::table('users')->where('id', $id)->first()
        );
    }
}