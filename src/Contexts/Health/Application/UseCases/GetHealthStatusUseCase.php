<?php

declare(strict_types=1);

namespace Hayabusa\Contexts\Health\Application\UseCases;

class GetHealthStatusUseCase
{
    public function execute(): array
    {
        return [
            'status' => 'ok',
            'framework' => 'Hayabusa',
            'php' => PHP_VERSION,
            'timestamp' => date('c'),
        ];
    }
}