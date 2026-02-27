<?php

declare(strict_types=1);

namespace Hayabusa\Contexts\Users\Infrastructure;

use Hayabusa\Contexts\Users\Application\UseCases\CreateUserUseCase;
use Hayabusa\Contexts\Users\Application\UseCases\GetUsersUseCase;
use Hayabusa\Http\Request;
use Hayabusa\Http\Response;

class UsersController
{
    public function __construct(
        private readonly GetUsersUseCase $getUsers,
        private readonly CreateUserUseCase $createUser,
    ) {
    }

    public function index(Request $request): Response
    {
        $users = $this->getUsers->execute();
        return Response::json(array_map(fn($u) => $u->toArray(), $users));
    }

    public function store(Request $request): Response
    {
        $body = $request->all();
        $user = $this->createUser->execute($body['name'], $body['email']);
        return Response::json($user->toArray(), 201);
    }
}