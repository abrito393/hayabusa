<?php

use Hayabusa\Application;
use Hayabusa\Contexts\Users\Infrastructure\UsersController;

$app = Application::getInstance();

$app->router()->group('/users', function ($router) use ($app) {
    $controller = $app->make(UsersController::class);

    $router->get('', [$controller, 'index']);
    $router->post('', [$controller, 'store']);
});