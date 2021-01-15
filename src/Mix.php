<?php

namespace TorMorten\Mix;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\HtmlString;
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
        // never try to resolve while in a local mix request
        if ($this->inLocalMixRequest()) {
            return '';
        }

        $pipes = [
            ResolveHmr::class,
            ResolveLocal::class,
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

    public function inLocalMixRequest()
    {
        return request()->is('mix/*');
    }
}