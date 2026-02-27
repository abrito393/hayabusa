# 01 — Routing

## ¿Qué hace?

El Router mapea métodos HTTP + paths a handlers (closures o controller actions).
Soporta parámetros dinámicos, grupos de rutas y middleware por ruta.

## Uso básico

```php
$app->router()->get('/users', [UsersController::class, 'index']);
$app->router()->post('/users', [UsersController::class, 'store']);
$app->router()->put('/users/{id}', [UsersController::class, 'update']);
$app->router()->patch('/users/{id}', [UsersController::class, 'update']);
$app->router()->delete('/users/{id}', [UsersController::class, 'destroy']);
```

### Closure directa

```php
$app->router()->get('/ping', fn(Request $req) => Response::json(['pong' => true]));
```

### Parámetros dinámicos

```php
$app->router()->get('/users/{id}', [UsersController::class, 'show']);

// En el controller:
public function show(Request $request): Response
{
    $id = $request->param('id');
    // ...
}
```

### Grupos de rutas

```php
$app->router()->group('/api/v1', function (Router $router) {
    $router->get('/users', [UsersController::class, 'index']);
    $router->post('/users', [UsersController::class, 'store']);
});
// Resultado: GET /api/v1/users, POST /api/v1/users
```

### Grupos anidados

```php
$app->router()->group('/api', function (Router $router) {
    $router->group('/v1', function (Router $router) {
        $router->get('/health', [HealthController::class, 'index']);
    });
});
// Resultado: GET /api/v1/health
```

### Middleware por ruta

```php
$app->router()
    ->get('/users', [UsersController::class, 'index'])
    ->middleware(new AuthMiddleware());
```

## Carga de rutas desde archivo

```php
// bootstrap/app.php
$app->loadRoutes(__DIR__ . '/../src/Contexts/Users/Infrastructure/routes.php');
$app->loadRoutes(__DIR__ . '/../src/Contexts/Health/Infrastructure/routes.php');
```

```php
// src/Contexts/Users/Infrastructure/routes.php
<?php
use Hayabusa\Http\Router;

/** @var Router $router */
$router->group('/api/v1', function (Router $router) {
    $router->get('/users', [UsersController::class, 'index']);
    $router->post('/users', [UsersController::class, 'store']);
});
```

## API reference

### Router

| Método | Firma | Descripción |
|--------|-------|-------------|
| `get` | `get(string $path, mixed $handler): Route` | Registra ruta GET |
| `post` | `post(string $path, mixed $handler): Route` | Registra ruta POST |
| `put` | `put(string $path, mixed $handler): Route` | Registra ruta PUT |
| `patch` | `patch(string $path, mixed $handler): Route` | Registra ruta PATCH |
| `delete` | `delete(string $path, mixed $handler): Route` | Registra ruta DELETE |
| `group` | `group(string $prefix, callable $callback): void` | Agrupa rutas bajo un prefijo |
| `dispatch` | `dispatch(Request $request): Response` | Despacha request al handler correcto |
| `routes` | `routes(): array` | Retorna todas las rutas registradas |

### Route (fluent)

| Método | Firma | Descripción |
|--------|-------|-------------|
| `middleware` | `middleware(MiddlewareInterface $mw): static` | Añade middleware a la ruta |

## Limitaciones / lo que NO hace

- No hay named routes (`route('users.index')`)
- No hay resource routes automáticas (`->resource('/users', ...)`)
- No hay rate limiting por ruta
- Los grupos no soportan middleware a nivel de grupo (solo por ruta individual)
- No hay caché de rutas

## Ejemplo completo

```php
<?php
// src/Contexts/Products/Infrastructure/routes.php

use Hayabusa\Http\Router;
use Hayabusa\Contexts\Products\Infrastructure\ProductsController;
use Hayabusa\Http\Middleware\AuthMiddleware;

/** @var Router $router */
$router->group('/api/v1/products', function (Router $router) {
    $router->get('/', [ProductsController::class, 'index']);
    $router->get('/{id}', [ProductsController::class, 'show']);
    $router->post('/', [ProductsController::class, 'store'])
        ->middleware(new AuthMiddleware());
    $router->put('/{id}', [ProductsController::class, 'update'])
        ->middleware(new AuthMiddleware());
    $router->delete('/{id}', [ProductsController::class, 'destroy'])
        ->middleware(new AuthMiddleware());
});
```