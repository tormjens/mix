<?php

namespace TorMorten\Mix\Resolvers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use TorMorten\Mix\Mix;
use TorMorten\Mix\Support\Packages;

class ResolveCdn
{
    protected $params;

    public function isDevEnvironment()
    {
        if (Str::contains(url()->current(), '.test') || Str::contains(url()->current(), '.staging') || Str::contains(url()->current(), '.dev')) {
            return true;
        }

        return app()->environment('local', 'testing');
    }

    public function handle(array $params, \Closure $next)
    {
        $this->params = $params;
        if (Config::get('mix.driver.cdn.url')) {
            $packages = $this->getInstalledPackages();
            if (($packages = $packages->where('name', $params['package']))->isNotEmpty()) {
                $url = $this->getMixUrl($this->isDevEnvironment() ? 'develop' : $packages->first()['version']);
                if (config('mix.cache.enabled', true)) {
                    Cache::put(resolve(ResolveCache::class)->cacheKey($params['package'], $params['filename']), $url);
                }
                return $url;
            }
        }

        return $next($params);
    }

    public function getMixUrl($version)
    {
        $manifest = $this->getManifest($version);
        $key = '/' . ltrim($this->params['filename'], '/');
        if (isset($manifest[$key])) {
            return $this->buildUrl($version, $manifest[$key]);
        }

        return null;
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

    protected function getManifest($version)
    {
        $package = $this->params['package'];
        $getManifest = function () use ($version, $package) {
            $path = $this->buildUrl($version, 'mix-manifest.json?time=' . now()->format('YmdHis'));
            try {
                $manifest = Http::get($path)->json();
                if (!$manifest) {
                    throw new \Exception('No manifest found.');
                }
                return $manifest;
            } catch (\Exception $e) {
                return [
                    Str::start($this->params['filename'], '/') => $this->params['filename']
                ];
            }
        };
        return $getManifest();
    }

    protected function getInstalledPackages()
    {
        return resolve(Packages::class)->values();
    }
}
