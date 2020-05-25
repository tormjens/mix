<?php

return [
    'home' => base_path(),
    'use_manifest' => env('MIX_USE_MANIFEST', true),
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
        'url' => env('MIX_LOCAL_URL', 'mix/{path}'),
        'middleware' => []
    ]
];