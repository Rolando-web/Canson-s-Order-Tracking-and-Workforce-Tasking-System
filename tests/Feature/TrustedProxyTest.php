<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // The proxy registration lives in an afterResolving hook on the HTTP kernel,
    // so it is only applied once the kernel has been resolved.
    app(Kernel::class);

    Route::get('/__scheme_probe', function () {
        return response()->json([
            'scheme' => request()->getScheme(),
            'current' => url()->current(),
        ]);
    });
});

it('registers the trust proxies middleware at the application level', function () {
    $property = (new ReflectionClass(TrustProxies::class))->getProperty('alwaysTrustProxies');
    $property->setAccessible(true);

    expect($property->getValue())->toBe('*');
});

it('reports https and builds https urls when Render forwards the proto header', function () {
    $this->get('/__scheme_probe', ['X-Forwarded-Proto' => 'https'])
        ->assertOk()
        ->assertJson([
            'scheme' => 'https',
            'current' => 'https://localhost/__scheme_probe',
        ]);
});

it('stays on http when no forwarded proto header is sent', function () {
    $this->get('/__scheme_probe')
        ->assertOk()
        ->assertJson([
            'scheme' => 'http',
            'current' => 'http://localhost/__scheme_probe',
        ]);
});

it('still trusts only the immediate proxy address rather than everything', function () {
    $request = Request::create('http://internal/__scheme_probe', 'GET', server: [
        'REMOTE_ADDR' => '10.0.0.9',
    ]);
    $request->headers->set('X-Forwarded-Proto', 'https');

    (new TrustProxies(app(), config()))->handle($request, fn ($r) => $r);

    expect($request->getTrustedProxies())->toBe(['10.0.0.9'])
        ->and($request->getScheme())->toBe('https');
});

it('keeps the health check route reachable', function () {
    $this->get('/up')->assertOk();
});
