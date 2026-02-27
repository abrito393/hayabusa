<?php

declare(strict_types=1);

namespace Hayabusa\Contexts\Users\Application\UseCases;

use Hayabusa\Contexts\Users\Domain\User;
use Hayabusa\Database\DB;

class GetUsersUseCase
{
    public function execute(): array
    {
        return array_map(
            fn(array $row) => User::fromArray($row),
            DB::table('users')->get()
        );
    }
}