<?php

namespace TorMorten\Mix\Resolvers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use TorMorten\Mix\Mix;
use TorMorten\Mix\Support\Packages;

class ResolveCdn
{
    protected $params;

    public function handle(array $params, \Closure $next)
    {
        $this->params = $params;
        if (Config::get('mix.driver.cdn.url')) {
            $packages = $this->getInstalledPackages();
            if (($packages = $packages->where('name', $params['package']))->isNotEmpty()) {
                return $this->getMixUrl(app()->environment('local') ? 'develop' : $packages->first()['version']);
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
        $getManifest = function () use ($version) {
            $path = $this->buildUrl($version, 'mix-manifest.json');
            try {
                $manifest = file_get_contents($path);
                return json_decode($manifest, true);
            } catch (\Exception $e) {
                return [
                    '/' . $this->params['filename'] => $this->params['filename']
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