<?php

namespace TorMorten\Mix\Traits;

use Illuminate\Support\Facades\Config;

class ResolveLocal
{
    protected $params;

    public function handle(array $params, \Closure $next)
    {
        $this->params = $params;

        if ($this->exists()) {
            return route('mix.show', ['path' => join('/', [
                $params['package'],
                $this->inManifest($params)
            ])]);
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

    protected function getFilePath($params, $path)
    {
        $filePath = [
            Config::get('mix.home'),
            'vendor',
            $params['package'],
            Config::get('mix.driver.local.directory'),
            $path
        ];

        return join('/', $filePath);
    }

    protected function getManifest($params)
    {
        try {
            $path = $this->getFilePath($params, 'mix-manifest.json');
            $manifest = file_get_contents($path);
            return json_decode($manifest, true);
        } catch (\Exception $e) {
            return [
                '/' . $params['filename'] => $params['filename']
            ];
        }
    }

    protected function inManifest($params)
    {
        $manifest = $this->getManifest($params);
        $key = '/' . ltrim($params['filename']);
        return isset($manifest[$key]) ? $manifest[$key] : false;
    }
}