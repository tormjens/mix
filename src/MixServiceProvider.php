<?php

namespace TorMorten\Mix;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use TorMorten\Mix\Http\Controllers\MixController;

class MixServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mix.php', 'mix');

        Route::middleware(Config::get('mix.route.middleware'))
            ->get(Config::get('mix.route.url'), MixController::class)
            ->name('mix.show')
            ->where('path', '.*');
    }

    public function register()
    {
        $this->app->singleton(Mix::class, function () {
            return new Mix();
        });
    }
}