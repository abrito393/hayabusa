<?php

namespace Hayabusa\Http\Middleware;

use Hayabusa\Http\Request;
use Hayabusa\Http\Response;

class MiddlewarePipeline
{
    private array $middleware = [];

    public function pipe(MiddlewareInterface $middleware): static
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function run(Request $request, callable $core): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            fn(callable $carry, MiddlewareInterface $mw) => fn(Request $req) => $mw->handle($req, $carry),
            $core
        );

        return $pipeline($request);
    }
}