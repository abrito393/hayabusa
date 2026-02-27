# 07 — CLI

## ¿Qué hace?

Consola de comandos para operaciones de base de datos y scaffolding.
Se invoca con `php hayabusa <comando>`. Cada comando implementa
`CommandInterface` y se registra en el `Kernel`.

## Comandos disponibles

```bash
php hayabusa migrate           # Ejecuta migraciones pendientes
php hayabusa migrate:rollback  # Revierte la última migración
```

## Uso básico

```bash
# Desde la raíz del proyecto
php hayabusa migrate

# Output esperado:
# Running migrations...
# ✓ CreateUsersTable
# ✓ CreateProductsTable
# Done.

php hayabusa migrate:rollback

# Output esperado:
# Rolling back...
# ✓ Rolled back CreateProductsTable
# Done.
```

## Crear un comando custom

```php
<?php
// src/Console/Commands/ClearCacheCommand.php

use Hayabusa\Console\Commands\CommandInterface;

class ClearCacheCommand implements CommandInterface
{
    public function name(): string
    {
        return 'cache:clear';
    }

    public function handle(array $args): void
    {
        // lógica del comando
        echo "Cache cleared.\n";
    }
}
```

### Registrar el comando en el Kernel

```php
<?php
// src/Console/Kernel.php — agregar en el array de comandos

$this->commands = [
    new MigrateCommand($runner),
    new RollbackCommand($runner),
    new ClearCacheCommand(),   // ← nuevo
];
```

### Ejecutar

```bash
php hayabusa cache:clear
```

## Pasar argumentos al comando

```bash
php hayabusa migrate --fresh
```

```php
class MigrateCommand implements CommandInterface
{
    public function handle(array $args): void
    {
        $fresh = in_array('--fresh', $args);

        if ($fresh) {
            // drop all + migrate
        }
        // ...
    }
}
```

## API reference

### CommandInterface

| Método | Firma | Descripción |
|--------|-------|-------------|
| `name` | `name(): string` | Nombre del comando (`migrate`, `cache:clear`) |
| `handle` | `handle(array $args): void` | Lógica del comando. `$args` = argv desde el index 2 |

### Kernel

| Método | Firma | Descripción |
|--------|-------|-------------|
| `handle` | `handle(): void` | Lee argv y despacha al comando correcto |

## Estructura del bin/hayabusa

```php
#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$app    = require __DIR__ . '/../bootstrap/app.php';
$kernel = new Hayabusa\Console\Kernel($app);
$kernel->handle();
```

## Limitaciones / lo que NO hace

- No hay output con colores ANSI (pendiente Fase 17)
- No hay preguntas interactivas (`ask`, `confirm`, `choice`)
- No hay progress bars
- No hay tabla output (`$this->table([...])`)
- No hay scheduling / cron integrado
- `php hayabusa make:context` y `make:migration` son Fase 17

## Ejemplo completo

```php
<?php
// src/Console/Commands/SeedCommand.php

use Hayabusa\Console\Commands\CommandInterface;
use Hayabusa\Database\DB;

class SeedCommand implements CommandInterface
{
    public function name(): string
    {
        return 'db:seed';
    }

    public function handle(array $args): void
    {
        echo "Seeding database...\n";

        DB::table('users')->insert([
            'name'  => 'Admin',
            'email' => 'admin@example.com',
        ]);

        DB::table('users')->insert([
            'name'  => 'Editor',
            'email' => 'editor@example.com',
        ]);

        echo "✓ Users seeded.\n";
        echo "Done.\n";
    }
}
```