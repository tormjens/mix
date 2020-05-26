<?php

namespace Tests;

use OSMAviation\Module\Providers\ServiceProvider;
use TorMorten\Mix\Mix;
use TorMorten\Mix\MixServiceProvider;

class MixTest extends \Orchestra\Testbench\TestCase
{
    public function test_cdn()
    {
        config([
            'mix.driver.cdn.url' => 'http://cdn.mix.test',
            'mix.home' => __DIR__ . '/../',
            'mix.vendor_dir' => 'tests/vendor'
        ]);

        $this->assertEquals('http://cdn.mix.test/framework/v7.12.0/css/app.css', resolve(Mix::class)->handle('css/app.css', 'laravel/framework'));
    }

    public function test_local()
    {
        config([
            'mix.driver.local.directory' => 'assets',
            'mix.home' => __DIR__,
            'mix.vendor_dir' => 'vendor'
        ]);

        $this->assertEquals('http://localhost/mix/foo/bar/css/app.css', resolve(Mix::class)->handle('css/app.css', 'foo/bar'));
    }

    public function test_hmr()
    {
        config([
            'mix.driver.hmr.directory' => 'assets',
            'mix.home' => __DIR__,
            'mix.vendor_dir' => 'hmr/vendor'
        ]);

        $this->assertEquals('http://localhost:8080/css/app.css', resolve(Mix::class)->handle('css/app.css', 'foo/bar'));
    }

    public function test_route()
    {
        config([
            'mix.driver.local.directory' => 'assets',
            'mix.home' => __DIR__,
            'mix.vendor_dir' => 'vendor'
        ]);

        $this
            ->get(route('mix.show', ['path' => 'foo/bar/css/app.css']))
            ->assertOk();

    }

    protected function getPackageProviders($app)
    {
        return [
            MixServiceProvider::class
        ];
    }
}