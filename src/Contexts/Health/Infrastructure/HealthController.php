<?php

declare(strict_types=1);

namespace Hayabusa\Contexts\Health\Infrastructure;

use Hayabusa\Contexts\Health\Application\UseCases\GetHealthStatusUseCase;
use Hayabusa\Http\Request;
use Hayabusa\Http\Response;

class HealthController
{
    public function __construct(
        private readonly GetHealthStatusUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return Response::json($this->useCase->execute());
    }
}