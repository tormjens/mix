<?php

use Illuminate\Support\Facades\App;
use TorMorten\Mix\Resolvers\ResolveCache;
use TorMorten\Mix\Resolvers\ResolveCdn;
use TorMorten\Mix\Resolvers\ResolveHmr;
use TorMorten\Mix\Resolvers\ResolveLocal;

return [
    'home' => base_path(),
    'vendor_dir' => env('MIX_VENDOR_DIR', 'vendor'),
    'use_manifest' => env('MIX_USE_MANIFEST', true),
    'run_in_tests' => false,
    'cache' => [
        'enabled' => env('MIX_CACHE_ENABLED', true),
        'key' => env('MIX_CACHE_KEY', basename(base_path())),
    ],
    'driver' => [
        'cdn' => [
            'include_vendor' => env('MIX_CDN_INCLUDE_VENDOR', false),
            'url' => env('MIX_CDN_URL', 'http://localhost'),
            'format' => env('MIX_CDN_FORMAT', '{url}/{vendor}/{package}/{version}/{path}'),
        ],
        'local' => [
            'directory' => env('MIX_LOCAL_DIR', 'dist'),
        ],
        'hmr' => [
            'directory' => env('MIX_HMR_DIR', env('MIX_LOCAL_DIR', 'dist')),
        ]
    ],
    'route' => [
        'enabled' => true,
        'url' => env('MIX_LOCAL_URL', 'mix/{path}'),
        'middleware' => []
    ],

    'in_production' => env('MIX_IN_PRODUCTION', App::isProduction()),

    'resolvers' => [
        'production' => [
            ResolveCache::class,
            ResolveLocal::class,
            ResolveCdn::class,
        ],
        'dev' => [
            ResolveHmr::class,
            ResolveLocal::class,
            ResolveCache::class,
            ResolveCdn::class,
        ],
        'local' => [
            ResolveHmr::class,
            ResolveLocal::class
        ]

    ],
];