<?php

namespace TorMorten\Mix\Resolvers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use TorMorten\Mix\Mix;
use TorMorten\Mix\Support\Packages;

class ResolveFallback
{
    protected $params;

    public function handle(array $params, \Closure $next)
    {
        return $this->buildUrl('develop', $params['filename']) . '?module=' . explode('/', $params['package'])[1] . '&cache=' . now()->format('Ymdhis');
    }

}
