<?php

namespace TorMorten\Mix\Resolvers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use TorMorten\Mix\Mix;
use TorMorten\Mix\Support\Packages;

class ResolveCache
{
    public function handle(array $params, \Closure $next)
    {
        if (config('mix.cache.enabled', true)) {
            if (Cache::has($this->cacheKey($params['package'], $params['filename']))) {
                return Cache::get($this->cacheKey($params['package'], $params['filename']));
            }
        }
        return $next($params);
    }

    public function cacheKeyPrefix()
    {
        return join(':', [
            'mix',
            config('mix.cache.key')
        ]);
    }

    public function cacheKey($package, $filename)
    {
        return join(':', [
            $this->cacheKeyPrefix(),
            md5($package . $filename)
        ]);
    }

}