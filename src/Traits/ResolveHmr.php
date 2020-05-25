<?php

namespace TorMorten\Mix\Traits;

use Illuminate\Support\Facades\Config;

class ResolveHmr
{
    public function handle(array $params, \Closure $next)
    {
        $path = join('/', [
            Config::get('mix.home'),
            'vendor',
            $params['package'],
            Config::get('mix.driver.hmr.directory'),
            'hot'
        ]);

        if (file_exists($path)) {
            $url = rtrim(trim(file_get_contents($path)), '/');
            return $url . '/' . ltrim($params['filename'], '/');
        }
        return $next($params);
    }
}