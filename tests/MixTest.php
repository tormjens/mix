<?php

use TorMorten\Mix\Mix;
use TorMorten\MixTests\TestCase;
use Illuminate\Support\Facades\Http;

uses(TestCase::class);

it('checks the cdn', function () {
    config([
        'mix.run_in_tests' => true,
        'mix.driver.cdn.url' => 'http://cdn.mix.test',
        'mix.home' => __DIR__ . '/../',
        'mix.vendor_dir' => 'tests/vendor'
    ]);

    Http::fake();

    $url = resolve(Mix::class)->handle('css/app.css', 'laravel/framework');

    $this->assertStringStartsWith('http://cdn.mix.test/framework/develop/css/app.css?cache=', (string) $url);
    Http::assertNothingSent();
});

it('resolves a vite asset through the cdn manifest', function () {
    config([
        'mix.run_in_tests' => true,
        'mix.driver.cdn.url' => 'http://cdn.mix.test',
        'mix.home' => __DIR__ . '/../',
        'mix.vendor_dir' => 'tests/vendor'
    ]);

    Http::fake([
        '*vite-manifest.json*' => Http::response(['/js/app.js' => '/js/app-ABC123.js']),
    ]);

    $url = resolve(Mix::class)->handle('js/app.js', 'laravel/framework', false, true);

    expect((string) $url)->toBe('http://cdn.mix.test/framework/develop/js/app-ABC123.js');
});

it('falls through when a vite asset has no manifest entry', function () {
    config([
        'mix.run_in_tests' => true,
        'mix.always_return' => false,
        'mix.driver.cdn.url' => 'http://cdn.mix.test',
        'mix.home' => __DIR__ . '/../',
        'mix.vendor_dir' => 'tests/vendor'
    ]);

    Http::fake([
        '*vite-manifest.json*' => Http::response([], 404),
    ]);

    $url = resolve(Mix::class)->handle('js/app.js', 'laravel/framework', false, true);

    expect((string) $url)->toBe('');
});

it('checks for hot module reloading', function () {
    config([
        'mix.run_in_tests' => true,
        'mix.driver.hmr.directory' => 'assets',
        'mix.home' => __DIR__,
        'mix.vendor_dir' => 'hmr/vendor'
    ]);

    $this->assertEquals('http://localhost:8080/css/app.css', resolve(Mix::class)->handle('css/app.css', 'foo/bar'));
});

it('checks locally', function () {
    config([
        'mix.run_in_tests' => true,
        'mix.driver.local.directory' => 'assets',
        'mix.home' => __DIR__,
        'mix.vendor_dir' => 'vendor'
    ]);

    $this->assertEquals('http://localhost/mix/foo/bar/css/app.css', resolve(Mix::class)->handle('css/app.css', 'foo/bar'));
});

it('verifies that the route works', function () {
    config([
        'mix.run_in_tests' => true,
        'mix.driver.local.directory' => 'assets',
        'mix.home' => __DIR__,
        'mix.vendor_dir' => 'vendor'
    ]);

    $this
        ->get(route('mix.show', ['path' => 'foo/bar/css/app.css']))
        ->assertOk();
});