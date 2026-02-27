# 06 — Validation

## ¿Qué hace?

Valida datos de entrada (body, params, query) contra un conjunto de reglas.
Retorna errores por campo o lanza una `HttpException 422` automáticamente.
Cada regla es una clase independiente que implementa `RuleInterface`.

## Uso básico

### Validar y lanzar excepción automática (422)

```php
// En el controller — si falla lanza HttpException 422
Validator::validate($request->body(), [
    'name'  => 'required|min:2|max:100',
    'email' => 'required|email',
]);
```

### Validar y manejar errores manualmente

```php
$result = Validator::make($request->body(), [
    'name'  => 'required|min:2',
    'email' => 'required|email',
]);

if ($result->fails()) {
    return Response::json(['errors' => $result->errors()], 422);
}

// $result->passes() → true si todo OK
```

## Sintaxis de reglas

### String con pipe (compacta)

```php
Validator::validate($data, [
    'name'     => 'required|min:2|max:100',
    'email'    => 'required|email',
    'age'      => 'required|integer|between:18,99',
    'password' => 'required|min:8',
    'role'     => 'required|in:admin,editor,viewer',
]);
```

### Array de instancias (máximo control)

```php
use Hayabusa\Validation\Rules\Required;
use Hayabusa\Validation\Rules\Between;
use Hayabusa\Validation\Rules\Regex;

Validator::validate($data, [
    'price' => [new Required(), new Between(0.01, 9999.99)],
    'slug'  => [new Required(), new Regex('/^[a-z0-9-]+$/')],
]);
```

## Reglas disponibles

### Presencia

| Regla | Sintaxis | Descripción |
|-------|----------|-------------|
| `Required` | `required` | El campo debe existir y no estar vacío |
| `Accepted` | `accepted` | Debe ser: `yes`, `on`, `1`, `true`, `1` (checkboxes) |

### Strings

| Regla | Sintaxis | Descripción |
|-------|----------|-------------|
| `MinLength` | `min:N` | Longitud mínima de N caracteres |
| `MaxLength` | `max:N` | Longitud máxima de N caracteres |
| `Alpha` | `alpha` | Solo letras (a-z, A-Z) |
| `AlphaNumeric` | `alpha_num` | Solo letras y números |
| `Slug` | `slug` | Letras minúsculas, números y guiones (`mi-slug-123`) |
| `Regex` | instancia | Valida contra patrón regex personalizado |

### Números

| Regla | Sintaxis | Descripción |
|-------|----------|-------------|
| `Integer` | `integer` | Debe ser un entero válido |
| `Numeric` | `numeric` | Debe ser numérico (int o float) |
| `Between` | `between:min,max` | Valor numérico entre min y max (inclusivo) |

### Formatos

| Regla | Sintaxis | Descripción |
|-------|----------|-------------|
| `Email` | `email` | Formato de email válido |
| `Url` | `url` | URL válida (requiere scheme: http/https) |
| `Ip` | `ip` | IPv4 o IPv6 válida |
| `Uuid` | `uuid` | UUID v4 válido |
| `Date` | `date` | Fecha en formato `Y-m-d` por default |
| `Json` | `json` | String JSON válido |

### Listas

| Regla | Sintaxis | Descripción |
|-------|----------|-------------|
| `In` | `in:a,b,c` | Valor debe estar en la lista |
| `NotIn` | `not_in:a,b` | Valor no debe estar en la lista |

### Confirmación

| Regla | Sintaxis | Descripción |
|-------|----------|-------------|
| `Confirmed` | instancia | Requiere campo `{field}_confirmation` con igual valor |

## Ejemplos por tipo de request

### Crear usuario

```php
Validator::validate($request->body(), [
    'name'                  => 'required|min:2|max:100|alpha',
    'email'                 => 'required|email|max:255',
    'password'              => 'required|min:8|max:72',
    'password_confirmation' => 'required',
    'role'                  => 'required|in:admin,editor,viewer',
]);
```

### Crear producto

```php
Validator::validate($request->body(), [
    'name'        => 'required|min:2|max:200',
    'slug'        => 'required|slug',
    'price'       => 'required|numeric|between:0.01,99999.99',
    'stock'       => 'required|integer|between:0,100000',
    'category_id' => 'required|integer',
    'metadata'    => 'json',
]);
```

### Parámetros de búsqueda

```php
Validator::validate($request->query(), [
    'page'     => 'integer|between:1,1000',
    'per_page' => 'integer|between:1,100',
    'sort'     => 'in:asc,desc',
    'status'   => 'in:active,inactive,pending',
]);
```

## API reference

### Validator

| Método | Firma | Descripción |
|--------|-------|-------------|
| `validate` | `validate(array $data, array $rules): void` | Valida o lanza HttpException 422 |
| `make` | `make(array $data, array $rules): ValidationResult` | Valida y retorna resultado |

### ValidationResult

| Método | Firma | Descripción |
|--------|-------|-------------|
| `passes` | `passes(): bool` | True si no hay errores |
| `fails` | `fails(): bool` | True si hay errores |
| `errors` | `errors(): array` | Array de errores por campo |

### RuleInterface

```php
interface RuleInterface
{
    public function passes(string $field, mixed $value): bool;
    public function message(string $field): string;
}
```

## Crear reglas custom

```php
<?php

use Hayabusa\Validation\Rules\RuleInterface;

class PhoneNumber implements RuleInterface
{
    public function passes(string $field, mixed $value): bool
    {
        return is_string($value) 
            && (bool) preg_match('/^\+?[1-9]\d{7,14}$/', $value);
    }

    public function message(string $field): string
    {
        return "The {$field} must be a valid phone number.";
    }
}

// Uso:
Validator::validate($data, [
    'phone' => [new Required(), new PhoneNumber()],
]);
```

## Limitaciones / lo que NO hace

- No hay validación de arrays anidados (`address.city`)
- No hay `sometimes` (validar solo si el campo existe)
- No hay `nullable` como regla (si no está present, Required lo captura)
- `Confirmed` requiere pasarse como instancia, no como string
- `Date` con formato custom requiere instancia: `new Date('d/m/Y')`
- `Between` e `In` con string solo soportan valores simples (sin comas en los valores)