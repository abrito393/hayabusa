# 02 — Container

## ¿Qué hace?

El Container es un IoC (Inversion of Control) container con autowiring via Reflection.
Resuelve dependencias automáticamente leyendo los type hints del constructor,
sin necesidad de registrar cada clase manualmente.

## Uso básico

### Autowiring (sin registrar nada)

```php
// Container resuelve UserRepository y sus dependencias automáticamente
$useCase = $container->make(CreateUserUseCase::class);
```

### bind — nueva instancia en cada llamada

```php
$container->bind(LoggerInterface::class, FileLogger::class);

// Cada make() retorna una instancia nueva
$logger1 = $container->make(LoggerInterface::class);
$logger2 = $container->make(LoggerInterface::class);
// $logger1 !== $logger2
```

### singleton — misma instancia siempre

```php
$container->singleton(DatabaseManager::class, function (Container $c) {
    return new DatabaseManager($c->make(Connection::class));
});

// Siempre retorna la misma instancia
$db1 = $container->make(DatabaseManager::class);
$db2 = $container->make(DatabaseManager::class);
// $db1 === $db2
```

### instance — registrar objeto ya construido

```php
$config = new Config(['debug' => true]);
$container->instance(Config::class, $config);

// Retorna exactamente ese objeto
$container->make(Config::class); // === $config
```

### Closure con dependencias

```php
$container->bind(PaymentGateway::class, function (Container $c) {
    return new StripeGateway(
        apiKey: 'sk_live_...',
        logger: $c->make(LoggerInterface::class)
    );
});
```

## Registro en Application

```php
$app = Application::create();

$app->container()->singleton(UserRepository::class, function () {
    return new PDOUserRepository(DB::connection());
});

$app->container()->bind(MailerInterface::class, SmtpMailer::class);
```

## API reference

| Método | Firma | Descripción |
|--------|-------|-------------|
| `bind` | `bind(string $abstract, Closure\|string $concrete): void` | Nueva instancia por cada `make()` |
| `singleton` | `singleton(string $abstract, Closure\|string $concrete): void` | Misma instancia siempre |
| `instance` | `instance(string $abstract, object $instance): void` | Registra objeto ya construido |
| `make` | `make(string $abstract): object` | Resuelve y retorna la instancia |
| `has` | `has(string $abstract): bool` | Verifica si hay binding o instancia |
| `flush` | `flush(): void` | Limpia todos los bindings e instancias |

## Orden de resolución

Cuando se llama `make($abstract)`:

1. ¿Hay una instancia cacheada? → retorna esa
2. ¿Hay un binding registrado? → ejecuta el closure o construye la clase
3. ¿No hay nada? → autowiring via Reflection

## Limitaciones / lo que NO hace

- No resuelve dependencias primitivas sin valor default (`string $name` sin default falla)
- No soporta etiquetas o tagged bindings
- No hay decorators ni interceptors
- No hay scoped bindings (por request, por coroutine)
- `flush()` limpia todo — usar con cuidado en contextos Swoole

## Ejemplo completo

```php
<?php
// bootstrap/app.php

use Hayabusa\Application;
use Hayabusa\Database\DatabaseManager;

$app = Application::create();

// Infraestructura
$app->container()->singleton(DatabaseManager::class, function () {
    $manager = DatabaseManager::getInstance();
    $manager->addConfig('default', [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../database/app.db',
    ]);
    return $manager;
});

// Repositorios
$app->container()->bind(
    UserRepositoryInterface::class,
    PDOUserRepository::class
);

// Use Cases — autowiring automático
// CreateUserUseCase recibe UserRepositoryInterface en su constructor
// el container lo resuelve solo
$useCase = $app->make(CreateUserUseCase::class);
```