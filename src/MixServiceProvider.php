<?php

namespace TorMorten\Mix;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use TorMorten\Mix\Http\Controllers\MixController;
use TorMorten\Mix\Resolvers\ResolveCdn;
use TorMorten\Mix\Resolvers\ResolveHmr;
use TorMorten\Mix\Resolvers\ResolveLocal;
use TorMorten\Mix\Support\Packages;

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

        $this->app->singleton(ResolveCdn::class);
        $this->app->singleton(ResolveHmr::class);
        $this->app->singleton(ResolveLocal::class);
        $this->app->singleton(Packages::class);
        $this->app->singleton(Mix::class);
    }
}