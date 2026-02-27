<?php

declare(strict_types=1);

namespace Hayabusa;

use Hayabusa\Container\Container;
use Hayabusa\Http\Request;
use Hayabusa\Http\Response;
use Hayabusa\Http\Router;
use Hayabusa\Exceptions\HttpException;
use Hayabusa\Database\DatabaseManager;

class Application
{
    private static ?Application $instance = null;

    public function __construct(
        private readonly Container $container,
        private readonly Router $router,
    ) {
    }

    // ─── Singleton ───────────────────────────────────────────────

    public static function create(): static
    {
        $container = new Container();
        $router = new Router();

        $app = new static($container, $router);

        $container->instance(Container::class, $container);
        $container->instance(Router::class, $router);
        $container->instance(Application::class, $app);

        static::$instance = $app;

        return $app;
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            throw new \RuntimeException('Application no ha sido inicializada. Llama a Application::create() primero.');
        }
        return static::$instance;
    }

    // ─── Routing ─────────────────────────────────────────────────

    public function loadRoutes(string $path): static
    {
        $router = $this->router;
        require $path;
        return $this;
    }

    public function router(): Router
    {
        return $this->router;
    }

    // ─── Container ───────────────────────────────────────────────

    public function container(): Container
    {
        return $this->container;
    }

    public function make(string $abstract): object
    {
        return $this->container->make($abstract);
    }

    // ─── Dispatch ────────────────────────────────────────────────

    public function handle(Request $request): Response
    {
        try {
            return $this->router->dispatch($request);
        } catch (HttpException $e) {
            return Response::json(['error' => $e->getMessage()], $e->statusCode());
        }
    }

    public function run(): void
    {
        $request = Request::fromGlobals();
        $response = $this->handle($request);
        $response->send();
    }

    public function withDatabase(array $config, string $name = 'default'): static
    {
        DatabaseManager::getInstance()->addConfig($name, $config);
        return $this;
    }
}