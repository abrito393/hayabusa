<?php

namespace Hayabusa\Http\Middleware;

use Hayabusa\Http\Request;
use Hayabusa\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}