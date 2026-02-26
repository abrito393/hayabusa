<?php

declare(strict_types=1);

use Hayabusa\Application;
use Hayabusa\Http\Request;

// ─── Tests ───────────────────────────────────────────────────────────────────

describe('Health endpoint', function () {

    beforeEach(function () {
        $this->app = Application::create();
        $this->app->loadRoutes(
            __DIR__ . '/../../../src/Contexts/Health/Infrastructure/routes.php'
        );
    });

    it('returns 200 with ok status', function () {
        $request = Request::fake('GET', '/health');
        $response = $this->app->handle($request);

        expect($response->getStatusCode())->toBe(200);
    });

    it('returns json with expected keys', function () {
        $request = Request::fake('GET', '/health');
        $response = $this->app->handle($request);

        $body = json_decode($response->getBody(), true);

        expect($body)->toHaveKeys(['status', 'framework', 'php', 'timestamp']);
        expect($body['status'])->toBe('ok');
        expect($body['framework'])->toBe('Hayabusa');
    });

    it('returns 404 for unknown route', function () {
        $request = Request::fake('GET', '/unknown');
        $response = $this->app->handle($request);

        expect($response->getStatusCode())->toBe(404);
    });

});