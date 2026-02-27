# 05 — Middleware

## ¿Qué hace?

Pipeline de middleware que envuelve el ciclo request/response.
Cada middleware puede modificar el request antes de llegar al handler,
y la response antes de salir. Implementación onion-model clásica.

## Uso básico

### Crear un middleware

```php
<?php

use Hayabusa\Http\Middleware\MiddlewareInterface;
use Hayabusa\Http\Request;
use Hayabusa\Http\Response;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // Antes del handler
        $response = $next($request);

        // Después del handler — modificar response
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
```

### Middleware global (aplica a todas las rutas)

```php
$app->addMiddleware(new CorsMiddleware());
$app->addMiddleware(new RequestLoggerMiddleware());
```

### Middleware por ruta

```php
$app->router()
    ->post('/users', [UsersController::class, 'store'])
    ->middleware(new AuthMiddleware());

$app->router()
    ->get('/admin/stats', [AdminController::class, 'stats'])
    ->middleware(new AuthMiddleware())
    ->middleware(new AdminOnlyMiddleware());
```

### Cortocircuitar el pipeline (retornar sin pasar al handler)

```php
class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $token = $request->header('Authorization');

        if (!$token || !$this->isValid($token)) {
            // No llama a $next — corta el pipeline
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
```

### Modificar el request antes del handler

```php
class JsonBodyMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // Enriquecer el request con datos parseados
        $enriched = $request->withAttribute('user_agent', $request->header('User-Agent'));
        return $next($enriched);
    }
}
```

## Orden de ejecución

Los middlewares se ejecutan en el orden en que se registran, en capas:

```
Request →  [Cors] → [Auth] → [Logger] → Handler
Response ← [Cors] ← [Auth] ← [Logger] ← Handler
```

```php
// Este orden:
$app->addMiddleware(new CorsMiddleware());   // 1ro
$app->addMiddleware(new AuthMiddleware());   // 2do
$app->addMiddleware(new LoggerMiddleware()); // 3ro

// Produce:
// Request:  Cors → Auth → Logger → Handler
// Response: Logger → Auth → Cors
```

## API reference

### MiddlewareInterface

| Método | Firma | Descripción |
|--------|-------|-------------|
| `handle` | `handle(Request $request, callable $next): Response` | Procesa el request/response |

### MiddlewarePipeline

| Método | Firma | Descripción |
|--------|-------|-------------|
| `pipe` | `pipe(MiddlewareInterface $mw): static` | Agrega middleware al pipeline |
| `run` | `run(Request $request, callable $core): Response` | Ejecuta el pipeline |

### Application

| Método | Firma | Descripción |
|--------|-------|-------------|
| `addMiddleware` | `addMiddleware(MiddlewareInterface $mw): static` | Middleware global |

### Route (fluent)

| Método | Firma | Descripción |
|--------|-------|-------------|
| `middleware` | `middleware(MiddlewareInterface $mw): static` | Middleware por ruta |

## Limitaciones / lo que NO hace

- No hay middleware groups con nombre (`->middlewareGroup('api')`)
- No hay prioridad explícita entre middlewares globales y de ruta
- No hay middleware exclusivo (excluir de ciertas rutas)
- No hay resolución de middleware via Container (se pasan instancias)

## Ejemplo completo

```php
<?php
// src/Http/Middleware/RateLimitMiddleware.php

use Hayabusa\Http\Middleware\MiddlewareInterface;
use Hayabusa\Http\Request;
use Hayabusa\Http\Response;

class RateLimitMiddleware implements MiddlewareInterface
{
    private static array $hits = [];

    public function __construct(
        private readonly int $maxRequests = 60,
        private readonly int $windowSeconds = 60,
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        $ip  = $request->header('X-Forwarded-For') ?? 'unknown';
        $key = $ip . ':' . floor(time() / $this->windowSeconds);

        self::$hits[$key] = (self::$hits[$key] ?? 0) + 1;

        if (self::$hits[$key] > $this->maxRequests) {
            return Response::json(['error' => 'Too Many Requests'], 429);
        }

        $response = $next($request);

        return $response->withHeader(
            'X-RateLimit-Remaining',
            (string) max(0, $this->maxRequests - self::$hits[$key])
        );
    }
}

// Uso:
$app->addMiddleware(new RateLimitMiddleware(maxRequests: 100, windowSeconds: 60));
```