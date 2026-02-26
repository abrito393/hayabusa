<?php

declare(strict_types=1);

namespace Hayabusa\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    // ─── Registro ────────────────────────────────────────────────

    public function bind(string $abstract, Closure|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, Closure|string $concrete): void
    {
        $this->bindings[$abstract] = function () use ($abstract, $concrete) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $concrete instanceof Closure
                    ? $concrete($this)
                    : $this->build($concrete);
            }
            return $this->instances[$abstract];
        };
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    // ─── Resolución ──────────────────────────────────────────────

    public function make(string $abstract): object
    {
        // Instancia ya resuelta
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Binding registrado
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract];
            return $concrete instanceof Closure
                ? $concrete($this)
                : $this->build($concrete);
        }

        // Autowiring directo
        return $this->build($abstract);
    }

    // ─── Construcción con Reflection ─────────────────────────────

    private function build(string $class): object
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException) {
            throw new ContainerException("No se puede resolver la clase [{$class}].");
        }

        if (!$reflection->isInstantiable()) {
            throw new ContainerException("La clase [{$class}] no es instanciable.");
        }

        $constructor = $reflection->getConstructor();

        // Sin constructor → instancia directa
        if ($constructor === null) {
            return new $class();
        }

        $dependencies = array_map(function ($param) use ($class) {
            $type = $param->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    return $param->getDefaultValue();
                }
                throw new ContainerException(
                    "No se puede resolver el parámetro [{$param->getName()}] en [{$class}]."
                );
            }

            return $this->make($type->getName());
        }, $constructor->getParameters());

        return $reflection->newInstanceArgs($dependencies);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }
}