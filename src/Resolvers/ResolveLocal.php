<?php

namespace TorMorten\Mix\Resolvers;

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

    public function inManifest($params)
    {
        $manifest = $this->getManifest($params);
        $key = '/' . ltrim($params['filename']);
        return isset($manifest[$key]) ? $manifest[$key] : false;
    }
}