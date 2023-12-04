<?php

namespace TorMorten\Mix\Resolvers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class ResolveLocal
{
    protected $params;

    public function handle(array $params, \Closure $next)
    {
        $this->params = $params;
        if ($this->exists()) {
            $path = $this->inManifest($params);
            $url = url('mix/' . join('/', [
                    $params['package'],
                    Str::startsWith($path, '/') ? substr($path, 1) : $path
                ])

            );
            Cache::put(resolve(ResolveCache::class)->cacheKey($params['package'], $params['filename']), $url);

            return $url;
        }

        return $next($params);
    }

    public function exists($params = null)
    {
        if (!$params) {
            $params = $this->params;
        }

        return $this->inManifest($params) && is_dir($this->getFilePath($params, ''));
    }

    public function getFilePath($params, $path)
    {
        $filePath = [
            rtrim(Config::get('mix.home'), '/'),
            trim(Config::get('mix.vendor_dir'), '/'),
            trim($params['package'], '/'),
            trim(Config::get('mix.driver.local.directory'), '/'),
            trim($path, '/')
        ];

        return join('/', $filePath);
    }

    protected function getManifest($params)
    {
        if ($params['forceLocal'] ?? false) {
            return [
                Str::start($params['filename'], '/') => $params['filename']
            ];
        }

        try {
            $path = $this->getFilePath($params, 'mix-manifest.json');
            $manifest = file_get_contents($path);
            return json_decode($manifest, true);
        } catch (\Exception $e) {
            return [
                Str::start($params['filename'], '/') => $params['filename']
            ];
        }
    }

    public function inManifest($params)
    {
        $manifest = $this->getManifest($params);
        $key = Str::start($params['filename'], '/');
        return isset($manifest[$key]) ? $manifest[$key] : false;
    }
}