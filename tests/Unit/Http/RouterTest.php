<?php

declare(strict_types=1);

use Hayabusa\Http\Router;
use Hayabusa\Http\Request;
use Hayabusa\Http\Response;
use Hayabusa\Exceptions\HttpException;

describe('Router', function () {

    beforeEach(function () {
        $this->router = new Router();
    });

    it('dispatches GET route', function () {
        $this->router->get('/users', fn(Request $r) => Response::json(['ok' => true]));

        $response = $this->router->dispatch(Request::fake('GET', '/users'));

        expect($response->status())->toBe(200)
            ->and($response->data())->toBe(['ok' => true]);
    });

    it('dispatches POST route', function () {
        $this->router->post('/users', fn(Request $r) => Response::json(['created' => true], 201));

        $response = $this->router->dispatch(Request::fake('POST', '/users'));

        expect($response->status())->toBe(201);
    });

    it('resolves route params', function () {
        $this->router->get('/users/{id}', fn(Request $r) => Response::json(['id' => $r->param('id')]));

        $response = $this->router->dispatch(Request::fake('GET', '/users/42'));

        expect($response->data())->toBe(['id' => '42']);
    });

    it('resolves nested route params', function () {
        $this->router->get('/users/{userId}/orders/{orderId}', function (Request $r) {
            return Response::json([
                'user' => $r->param('userId'),
                'order' => $r->param('orderId'),
            ]);
        });

        $response = $this->router->dispatch(Request::fake('GET', '/users/1/orders/99'));

        expect($response->data())->toBe(['user' => '1', 'order' => '99']);
    });

    it('groups routes with prefix', function () {
        $this->router->group('/v1', function (Router $r) {
            $r->get('/products', fn() => Response::json(['v' => 1]));
        });

        $response = $this->router->dispatch(Request::fake('GET', '/v1/products'));

        expect($response->status())->toBe(200);
    });

    it('throws 404 for unknown route', function () {
        expect(fn() => $this->router->dispatch(Request::fake('GET', '/unknown')))
            ->toThrow(HttpException::class);
    });

    it('does not match wrong method', function () {
        $this->router->get('/users', fn() => Response::json([]));

        expect(fn() => $this->router->dispatch(Request::fake('POST', '/users')))
            ->toThrow(HttpException::class);
    });

});