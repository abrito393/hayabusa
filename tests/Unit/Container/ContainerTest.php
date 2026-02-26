<?php

declare(strict_types=1);

use Hayabusa\Container\Container;
use Hayabusa\Container\ContainerException;

// ─── Stubs ───────────────────────────────────────────────────────────────────

class StubNoDeps
{
}

class StubWithDep
{
    public function __construct(public readonly StubNoDeps $dep)
    {
    }
}

class StubWithDefault
{
    public function __construct(public readonly string $name = 'hayabusa')
    {
    }
}

interface StubContract
{
}
class StubImpl implements StubContract
{
}

// ─── Tests ───────────────────────────────────────────────────────────────────

describe('Container', function () {

    beforeEach(function () {
        $this->container = new Container();
    });

    it('autowires a class with no dependencies', function () {
        $obj = $this->container->make(StubNoDeps::class);
        expect($obj)->toBeInstanceOf(StubNoDeps::class);
    });

    it('autowires a class with dependencies', function () {
        $obj = $this->container->make(StubWithDep::class);
        expect($obj)->toBeInstanceOf(StubWithDep::class);
        expect($obj->dep)->toBeInstanceOf(StubNoDeps::class);
    });

    it('resolves default primitive values', function () {
        $obj = $this->container->make(StubWithDefault::class);
        expect($obj->name)->toBe('hayabusa');
    });

    it('resolves a closure binding', function () {
        $this->container->bind(StubContract::class, fn() => new StubImpl());
        $obj = $this->container->make(StubContract::class);
        expect($obj)->toBeInstanceOf(StubImpl::class);
    });

    it('resolves singleton only once', function () {
        $this->container->singleton(StubNoDeps::class, fn() => new StubNoDeps());
        $a = $this->container->make(StubNoDeps::class);
        $b = $this->container->make(StubNoDeps::class);
        expect($a)->toBe($b);
    });

    it('resolves a pre-built instance', function () {
        $obj = new StubNoDeps();
        $this->container->instance(StubNoDeps::class, $obj);
        expect($this->container->make(StubNoDeps::class))->toBe($obj);
    });

    it('has() returns true for registered bindings', function () {
        $this->container->bind(StubContract::class, fn() => new StubImpl());
        expect($this->container->has(StubContract::class))->toBeTrue();
        expect($this->container->has(StubNoDeps::class))->toBeFalse();
    });

    it('throws ContainerException for non-existent class', function () {
        expect(fn() => $this->container->make('NonExistentClass'))
            ->toThrow(ContainerException::class);
    });

    it('flushes all bindings and instances', function () {
        $this->container->bind(StubContract::class, fn() => new StubImpl());
        $this->container->flush();
        expect($this->container->has(StubContract::class))->toBeFalse();
    });

});