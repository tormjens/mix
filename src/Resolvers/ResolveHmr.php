<?php

namespace TorMorten\Mix\Resolvers;

use Illuminate\Support\Facades\Config;

class ResolveHmr
{
    public function handle(array $params, \Closure $next)
    {
        $path = join('/', [
            Config::get('mix.home'),
            Config::get('mix.vendor_dir'),
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