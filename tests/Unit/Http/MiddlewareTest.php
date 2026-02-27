<?php

declare(strict_types=1);

use Hayabusa\Http\Middleware\MiddlewareInterface;
use Hayabusa\Http\Middleware\MiddlewarePipeline;
use Hayabusa\Http\Request;
use Hayabusa\Http\Response;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function makeRequest(): Request
{
    return Request::fake('GET', '/test');
}

function coreHandler(): callable
{
    return fn(Request $req) => Response::json(['handler' => 'reached']);
}

// ─── Tests ───────────────────────────────────────────────────────────────────

test('pipeline runs core when no middleware', function () {
    $pipeline = new MiddlewarePipeline();
    $response = $pipeline->run(makeRequest(), coreHandler());

    expect($response->getStatusCode())->toBe(200)
        ->and($response->data())->toBe(['handler' => 'reached']);
});

test('middleware can modify request before core', function () {
    $mw = new class implements MiddlewareInterface {
        public function handle(Request $request, callable $next): Response
        {
            $modified = $request->withParams(['injected' => 'yes']);
            return $next($modified);
        }
    };

    $pipeline = (new MiddlewarePipeline())->pipe($mw);

    $response = $pipeline->run(makeRequest(), function (Request $req) {
        return Response::json(['injected' => $req->param('injected')]);
    });

    expect($response->data())->toBe(['injected' => 'yes']);
});

test('middleware can short-circuit and return early', function () {
    $authMw = new class implements MiddlewareInterface {
        public function handle(Request $request, callable $next): Response
        {
            return Response::unauthorized();
        }
    };

    $pipeline = (new MiddlewarePipeline())->pipe($authMw);
    $response = $pipeline->run(makeRequest(), coreHandler());

    expect($response->getStatusCode())->toBe(401);
});

test('multiple middleware execute in correct order', function () {
    $log = [];

    $first = new class ($log) implements MiddlewareInterface {
        public function __construct(private array &$log)
        {}
        public function handle(Request $request, callable $next): Response
        {
            $this->log[] = 'first:before';
            $response = $next($request);
            $this->log[] = 'first:after';
            return $response;
        }
    };

    $second = new class ($log) implements MiddlewareInterface {
        public function __construct(private array &$log)
        {}
        public function handle(Request $request, callable $next): Response
        {
            $this->log[] = 'second:before';
            $response = $next($request);
            $this->log[] = 'second:after';
            return $response;
        }
    };

    $pipeline = (new MiddlewarePipeline())->pipe($first)->pipe($second);
    $pipeline->run(makeRequest(), coreHandler());

    expect($log)->toBe(['first:before', 'second:before', 'second:after', 'first:after']);
});

test('middleware integrates with Application::handle()', function () {
    $app = \Hayabusa\Application::create();

    $app->addMiddleware(new class implements MiddlewareInterface {
        public function handle(Request $request, callable $next): Response
        {
            return Response::json(['blocked' => true], 403);
        }
    });

    $response = $app->handle(makeRequest());

    expect($response->getStatusCode())->toBe(403)
        ->and($response->data())->toBe(['blocked' => true]);
});