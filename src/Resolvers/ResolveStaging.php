<?php

namespace TorMorten\Mix\Resolvers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use TorMorten\Mix\Mix;
use TorMorten\Mix\Support\Packages;

class ResolveStaging
{
    protected $params;

    public function handle(array $params, \Closure $next)
    {
        $this->params = $params;
        if (Config::get('mix.driver.cdn.url') && Config::get('mix.force_staging')) {
            return $this->buildUrl('develop', $params['filename']) . '?module=' . explode('/', $params['package'])[1] . '&cache=' . now()->format('Ymdhis');
        }

        return $next($params);
    }

    protected function buildUrl($version, $path)
    {
        [$vendor, $package] = explode('/', $this->params['package']);

        $find = ['{url}', '{vendor}', '{package}', '{version}', '{path}'];
        $replace = [
            rtrim(Config::get('mix.driver.cdn.url'), '/'),
            $vendor,
            $package,
            $version,
            ltrim($path, '/'),
        ];

        $urlFormat = Config::get('mix.driver.cdn.format');
        if (!Config::get('mix.driver.cdn.include_vendor')) {
            $urlFormat = str_replace('{vendor}/', '', $urlFormat);
        }

        return str_replace(
            $find,
            $replace,
            $urlFormat
        );
    }

}
