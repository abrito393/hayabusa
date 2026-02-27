# 03 — Database

## ¿Qué hace?

Capa de base de datos sin ORM. Expone un QueryBuilder fluent para
escribir queries con control total del SQL, sin magia ni active record.
Soporta múltiples conexiones nombradas.

## Uso básico

### Configurar conexión

```php
$app->withDatabase([
    'driver'   => 'sqlite',
    'database' => __DIR__ . '/database/app.db',
]);

// MySQL
$app->withDatabase([
    'driver'   => 'mysql',
    'host'     => 'localhost',
    'port'     => 3306,
    'database' => 'hayabusa',
    'username' => 'root',
    'password' => 'secret',
    'charset'  => 'utf8mb4',
]);
```

### Múltiples conexiones

```php
$app->withDatabase($primaryConfig, 'default');
$app->withDatabase($replicaConfig, 'replica');

// Usar conexión específica
DB::connection('replica')->table('users')->get();
```

### QueryBuilder — SELECT

```php
// Todos los registros
$users = DB::table('users')->get();

// Con condiciones
$user = DB::table('users')
    ->where('email', '=', 'john@example.com')
    ->first();

// Múltiples where
$results = DB::table('orders')
    ->where('status', '=', 'pending')
    ->where('total', '>', 100)
    ->get();

// Select específico
$names = DB::table('users')
    ->select(['id', 'name', 'email'])
    ->get();

// Order y limit
$recent = DB::table('posts')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

// Find por id
$user = DB::table('users')->find(42);
```

### QueryBuilder — INSERT

```php
$id = DB::table('users')->insert([
    'name'  => 'John Doe',
    'email' => 'john@example.com',
]);
// Retorna el last insert id
```

### QueryBuilder — UPDATE

```php
$affected = DB::table('users')
    ->where('id', '=', 42)
    ->update(['name' => 'Jane Doe']);
// Retorna filas afectadas
```

### QueryBuilder — DELETE

```php
$affected = DB::table('users')
    ->where('id', '=', 42)
    ->delete();
```

### Raw queries

```php
$results = DB::connection()->query(
    'SELECT * FROM users WHERE created_at > ?',
    ['2024-01-01']
);
```

## API reference

### DB (facade estática)

| Método | Firma | Descripción |
|--------|-------|-------------|
| `table` | `table(string $table): QueryBuilder` | QueryBuilder para la tabla |
| `connection` | `connection(string $name = 'default'): Connection` | Retorna la conexión PDO |

### QueryBuilder

| Método | Firma | Descripción |
|--------|-------|-------------|
| `select` | `select(array $columns): static` | Columnas a seleccionar |
| `where` | `where(string $col, string $op, mixed $val): static` | Condición WHERE |
| `orderBy` | `orderBy(string $col, string $dir = 'ASC'): static` | Orden |
| `limit` | `limit(int $n): static` | Límite de resultados |
| `get` | `get(): array` | Ejecuta SELECT, retorna array |
| `first` | `first(): ?array` | Primer resultado o null |
| `find` | `find(int $id): ?array` | Busca por id |
| `insert` | `insert(array $data): int` | INSERT, retorna last insert id |
| `update` | `update(array $data): int` | UPDATE, retorna filas afectadas |
| `delete` | `delete(): int` | DELETE, retorna filas afectadas |

## Limitaciones / lo que NO hace

- No hay ORM ni Eloquent-style models
- No hay eager loading ni relaciones
- No hay query logging activado por defecto
- No hay transacciones fluent (`DB::transaction(fn() => ...)`)
- `where` encadena siempre con AND — no hay `orWhere`
- No hay joins fluent

## Ejemplo completo

```php
<?php
// src/Contexts/Users/Infrastructure/PDOUserRepository.php

use Hayabusa\Database\DB;

class PDOUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        $row = DB::table('users')->find($id);
        return $row ? User::fromArray($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $row = DB::table('users')
            ->where('email', '=', $email)
            ->first();
        return $row ? User::fromArray($row) : null;
    }

    public function save(User $user): int
    {
        return DB::table('users')->insert([
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }

    public function all(): array
    {
        return array_map(
            fn($row) => User::fromArray($row),
            DB::table('users')->orderBy('created_at', 'DESC')->get()
        );
    }
}
```