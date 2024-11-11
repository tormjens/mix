<?php

namespace TorMorten\Mix;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;
use TorMorten\Mix\Resolvers\ResolveCache;
use TorMorten\Mix\Resolvers\ResolveCdn;
use TorMorten\Mix\Resolvers\ResolveHmr;
use TorMorten\Mix\Resolvers\ResolveLocal;
use TorMorten\Mix\Resolvers\ResolveFallback;

class Mix
{
    public function __construct($home = null)
    {
        if (!$home) {
            $home = base_path();
        }

        $this->home = $home;
    }

    public function handle($filename, $package, $forceLocal = false)
    {
        if (!$this->shouldProcess()) {
            return '';
        }

        $pipes = Config::get('mix.resolvers.dev');

        if (Config::get('mix.in_production')) {
            $pipes = Config::get('mix.resolvers.production');
        }

        if ($forceLocal) {
            $pipes = Config::get('mix.resolvers.local');
        }

        if (Config::get('mix.always_return', true)) {
            $pipes[] = ResolveFallback::class;
        }

        return resolve(Pipeline::class)
            ->send(compact('filename', 'package', 'forceLocal'))
            ->through($pipes)
            ->then(fn($params) => new HtmlString(is_string($params) ? $params : ''));
    }

    public function shouldProcess()
    {
        if (!$this->inLocalMixRequest() && app()->runningUnitTests()) {
            return config('mix.run_in_tests', false);
        }

        return !$this->inLocalMixRequest();
    }

    public function inLocalMixRequest()
    {
        return request()->is('mix/*');
    }
}
