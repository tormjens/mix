<?php

namespace TorMorten\Mix;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use TorMorten\Mix\Resolvers\ResolveCdn;
use TorMorten\Mix\Resolvers\ResolveHmr;
use TorMorten\Mix\Resolvers\ResolveLocal;
use TorMorten\Mix\Support\Packages;

class VariableAsset
{
    public function handle($filename, $package)
    {
        // never try to resolve while in a local mix request
        if ($this->inLocalMixRequest()) {
            return '';
        }

        if ($url = $this->findLocally($filename, $package)) {
            return $url;
        }

        return $this->findRemotely($filename, $package);
    }

    protected function findLocally($filename, $package)
    {
        $filePath = [
            rtrim(Config::get('mix.home'), '/'),
            trim(Config::get('mix.vendor_dir'), '/'),
            trim($package, '/'),
            trim(Config::get('mix.driver.local.directory'), '/'),
            trim($filename, '/')
        ];

        $filePath = join('/', $filePath);

        if (!file_exists($filePath)) {
            return false;
        }

        return url('mix/' . join('/', [
                $package,
                Str::startsWith($filename, '/') ? substr($filename, 1) : $filename
            ])
        );


    }

    protected function findRemotely($filename, $package)
    {
        $packageInfo = resolve(Packages::class)->where('name', $package)->first();

        if (!$packageInfo) {
            return '';
        }

        [$vendor, $package] = explode('/', $package);

        $find = ['{url}', '{vendor}', '{package}', '{version}', '{path}'];
        $replace = [
            rtrim(Config::get('mix.driver.cdn.url'), '/'),
            $vendor,
            $package,
            app()->environment('local', 'testing') ? 'develop' : $packageInfo['version'],
            ltrim($filename, '/'),
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

    public function inLocalMixRequest()
    {
        return request()->is('mix/*');
    }
}