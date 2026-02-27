# 04 — Migrations

## ¿Qué hace?

Sistema de migraciones con versionado. Crea y modifica tablas
usando el Blueprint fluent. Registra migraciones ejecutadas
para no repetirlas. Soporta rollback.

## Uso básico

### Crear una migración

```php
<?php
// src/Contexts/Users/Infrastructure/Migrations/CreateUsersTable.php

use Hayabusa\Database\Schema\Migration;
use Hayabusa\Database\Schema\Schema;
use Hayabusa\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique('email', 'users');
            $table->string('password');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('users');
    }
}
```

### Ejecutar migraciones por CLI

```bash
php hayabusa migrate
php hayabusa migrate:rollback
```

### Ejecutar migraciones por código

```php
use Hayabusa\Database\MigrationRunner;

$runner = new MigrationRunner();
$runner->register(new CreateUsersTable());
$runner->register(new CreateProductsTable());
$runner->run();    // ejecuta pendientes
$runner->rollback(); // revierte la última
```

## Blueprint — tipos disponibles

### Primary Keys
```php
$table->id();                    // INTEGER PRIMARY KEY AUTOINCREMENT
$table->uuid('id');              // VARCHAR(36) PRIMARY KEY
```

### Strings
```php
$table->string('name');          // VARCHAR(255)
$table->string('code', 10);      // VARCHAR(10)
$table->char('iso', 2);          // CHAR(2)
$table->text('bio');             // TEXT
$table->mediumText('content');   // MEDIUMTEXT
$table->longText('body');        // LONGTEXT
```

### Números enteros
```php
$table->integer('views');
$table->tinyInteger('rating');
$table->smallInteger('year');
$table->bigInteger('revenue');
$table->unsignedInteger('count');
$table->unsignedBigInteger('hits');
```

### Decimales
```php
$table->float('lat');
$table->double('price', 15, 8);
$table->decimal('amount', 10, 2);
```

### Booleanos
```php
$table->boolean('active');       // TINYINT(1) DEFAULT 0
```

### Fechas
```php
$table->date('birthday');
$table->time('opens_at');
$table->dateTime('published_at');
$table->timestamp('verified_at');
$table->timestamps();            // created_at + updated_at
$table->softDeletes();           // deleted_at NULL
```

### Otros
```php
$table->json('metadata');        // TEXT (SQLite no tiene JSON nativo)
$table->binary('avatar');        // BLOB
$table->foreignId('user_id');    // INTEGER NOT NULL
```

### Modificadores (fluent, aplican al último campo)
```php
$table->string('phone')->nullable();
$table->string('role')->default('user');
$table->string('slug')->notNull();
$table->integer('score')->unsigned();
```

### Índices
```php
$table->unique('email', 'users');
$table->index('created_at', 'users');
$table->compositeIndex(['tenant_id', 'email'], 'users');
```

## API reference

### Schema

| Método | Firma | Descripción |
|--------|-------|-------------|
| `create` | `create(string $table, callable $callback): void` | Crea una tabla |
| `drop` | `drop(string $table): void` | Elimina una tabla |
| `dropIfExists` | `dropIfExists(string $table): void` | Elimina si existe |

### Migration (clase base)

| Método | Firma | Descripción |
|--------|-------|-------------|
| `up` | `up(): void` | Aplica la migración |
| `down` | `down(): void` | Revierte la migración |

### MigrationRunner

| Método | Firma | Descripción |
|--------|-------|-------------|
| `register` | `register(Migration $m): void` | Registra una migración |
| `run` | `run(): void` | Ejecuta migraciones pendientes |
| `rollback` | `rollback(): void` | Revierte la última migración |

## Limitaciones / lo que NO hace

- No hay `alter table` (no se puede modificar columnas existentes)
- No hay `rename table`
- No hay migraciones automáticas desde models/entidades
- El orden de ejecución depende del orden de `register()`
- No hay squash de migraciones

## Ejemplo completo

```php
<?php
// src/Contexts/Products/Infrastructure/Migrations/CreateProductsTable.php

use Hayabusa\Database\Schema\Migration;
use Hayabusa\Database\Schema\Schema;
use Hayabusa\Database\Schema\Blueprint;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->string('name');
            $table->string('slug')->notNull();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug', 'products');
            $table->index('category_id', 'products');
            $table->compositeIndex(['active', 'created_at'], 'products');
        });
    }

    public function down(): void
    {
        Schema::drop('products');
    }
}
```