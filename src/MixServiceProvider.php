<?php

namespace TorMorten\Mix;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MixServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mix.php', 'mix');

        if (Config::get('mix.route.enabled')) {
            Route::middleware(Config::get('mix.route.middleware'))
                ->get(Config::get('mix.route.url'), Http\Controllers\MixController::class)
                ->name('mix.show')
                ->where('path', '.*');
        }

        $this->app->singleton(Resolvers\ResolveCache::class);
        $this->app->singleton(Resolvers\ResolveCdn::class);
        $this->app->singleton(Resolvers\ResolveHmr::class);
        $this->app->singleton(Resolvers\ResolveLocal::class);
        $this->app->singleton(Support\Packages::class);
        $this->app->singleton(Mix::class);
    }
}