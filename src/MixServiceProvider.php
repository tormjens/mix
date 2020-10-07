<?php

namespace TorMorten\Mix;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use TorMorten\Mix\Http\Controllers\MixController;
use TorMorten\Mix\Resolvers\ResolveCdn;
use TorMorten\Mix\Resolvers\ResolveHmr;
use TorMorten\Mix\Resolvers\ResolveLocal;

class MixServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {

        $this->mergeConfigFrom(__DIR__ . '/../config/mix.php', 'mix');

        Route::middleware(Config::get('mix.route.middleware'))
            ->get(Config::get('mix.route.url'), MixController::class)
            ->name('mix.show')
            ->where('path', '.*');

        $this->app->singleton(ResolveCdn::class, function () {
            return new ResolveCdn();
        });

        $this->app->singleton(ResolveHmr::class, function () {
            return new ResolveHmr();
        });

        $this->app->singleton(ResolveLocal::class, function () {
            return new ResolveLocal();
        });

        $this->app->singleton(Mix::class, function () {
            return new Mix();
        });
    }
}