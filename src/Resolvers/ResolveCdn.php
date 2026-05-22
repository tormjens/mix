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
    protected const DEFAULT_DEV_CACHE_MINUTES = 30;
    protected const MIN_DEV_CACHE_MINUTES = 1;
    protected const CACHE_BUST_TOKEN_LENGTH = 12;

    protected $params;
    protected $resolveCache;

    public function __construct(ResolveCache $resolveCache)
    {
        $this->resolveCache = $resolveCache;
    }

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
                $isDevEnvironment = $this->isDevEnvironment();
                $version = $isDevEnvironment ? 'develop' : $packages->first()['version'];
                $url = $isDevEnvironment ? $this->buildDevMixUrl($version) : $this->getMixUrl($version);
                if (config('mix.cache.enabled', true)) {
                    $cacheKey = $this->resolveCache->cacheKey($params['package'], $params['filename']);
                    if ($isDevEnvironment) {
                        Cache::put($cacheKey, $url, now()->addMinutes($this->devCacheMinutes()));
                    } else {
                        Cache::put($cacheKey, $url);
                    }
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

    public function buildDevMixUrl($version)
    {
        $url = $this->buildUrl($version, ltrim($this->params['filename'], '/'));
        $separator = Str::contains($url, '?') ? '&' : '?';
        $cacheBustValue = $this->getDevCacheBustValue();

        return $url . $separator . 'cache=' . $cacheBustValue;
    }

    protected function getDevCacheBustValue()
    {
        if (!config('mix.cache.enabled', true)) {
            return Str::random(self::CACHE_BUST_TOKEN_LENGTH);
        }

        return Cache::remember(
            $this->devCacheBustCacheKey(),
            now()->addMinutes($this->devCacheMinutes()),
            fn () => Str::random(self::CACHE_BUST_TOKEN_LENGTH)
        );
    }

    protected function devCacheBustCacheKey()
    {
        return implode(':', [
            $this->resolveCache->cacheKeyPrefix(),
            'cdn',
            'develop',
            'cache_bust',
            md5($this->params['package'] . $this->params['filename']),
        ]);
    }

    protected function devCacheMinutes()
    {
        return max(
            (int) Config::get('mix.driver.cdn.develop_cache_minutes', self::DEFAULT_DEV_CACHE_MINUTES),
            self::MIN_DEV_CACHE_MINUTES
        );
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
