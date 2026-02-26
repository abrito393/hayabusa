<?php

declare(strict_types=1);

use Hayabusa\Contexts\Health\Infrastructure\HealthController;

$router->get('/health', HealthController::class);