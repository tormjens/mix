<?php

namespace TorMorten\Mix;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\HtmlString;
use TorMorten\Mix\Resolvers\ResolveCache;
use TorMorten\Mix\Resolvers\ResolveCdn;
use TorMorten\Mix\Resolvers\ResolveHmr;
use TorMorten\Mix\Resolvers\ResolveLocal;

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

        $pipes = [
            ResolveHmr::class,
            ResolveLocal::class,
            ResolveCache::class,
            ResolveCdn::class,
        ];

        if ($forceLocal) {
            $pipes = [ResolveLocal::class];
        }

        return resolve(Pipeline::class)
            ->send(compact('filename', 'package', 'forceLocal'))
            ->through($pipes)
            ->then(fn($params) => new HtmlString(is_string($params) ? $params : ''));
    }

    public function shouldProcess()
    {
        if (!$this->inLocalMixRequest() && app()->runningUnitTests()) {
            return config('mix.run_in_tests', true);
        }

        return false;
    }

    public function inLocalMixRequest()
    {
        return request()->is('mix/*');
    }
}