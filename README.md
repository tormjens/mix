# Extended Mix

Extended Mix was born in an application that was entirely modular. We needed to load assets from our modules (Composer
packages) while also making it possible to hot reload and host assets on a CDN.

This application had modules that were regular Composer packages, so the folder structure would be like.

```
app/
vendor/
    foo/
        bar/
```

When pushing to master it was setup to compile assets and push them up to AWS S3, which had a Cloudfront set up.

Locally we wanted to still be able to use HMR (hot reloading) or just compiling the assets to test.

In production we wanted assets to be found via our CDN.

# Installation

Grab the package via Composer

```
composer require tormjens/extended-mix
```

This package does not have auto-discovery enabled as the application it is built for loads it and modifies the config
for it at runtime. To enable the package you either have to register it via your `config/app.php` or within a
serviceprovider loaded in your "main module".

This example is taken out of the `core` module of my application.

```php
public function register()
{
    $this->app->register(MixServiceProvider::class);
    $config = config('mix');
    $config['driver']['cdn'] = [
        'include_vendor' => env('MIX_CDN_INCLUDE_VENDOR', false),
        'url' => env('MIX_CDN_URL', 'https://cdn.foo.com'),
        'format' => env('MIX_CDN_FORMAT', '{url}/{package}/{version}/{path}'),
    ];
    
    config(['mix' => $config]);
}
```

## Getting the URL of an asset

It is actually very simple. Wherever you need the URL to an asset you'll simply use:

```php
extendedMix('/css/app.css', 'foo/bar');
```

Behind the scenes this will pipe the requested file through a series of resolvers to figure out the URL:

1. It will check to see if the requested file can be requested via hot reloading (very neat locally), and then return
   the URL to the file with hot reloading enabled.
1. It will check whether there's a local copy of the file and the return the URL to a proxied asset.
1. It will check the CDN (if configured), and return the URL.

Also, it does support mix-manifest so you can version your assets.

For cases where some assets are in the manifest, and some not (when you've used the `copy`method), you can use
the `variableAsset` function which will look for the asset locally first, then fallback to the CDN.

```php 
variableAsset('/images/someImage.png', 'foo/bar');
```

## Proxied local assets

This package provides a convenient way of proxying assets stored locally via a route named `mix.show`. While this is
very neat when testing locally, it is not ideal for production as it bootstraps the entire application just to serve a
asset. You should consider adding a rule in your webserver that mimmicks this functionality.

## CDN Assets

CDN is supported out of the box, but a convention is necessary to correctly display your assets. By default, we use the
format `/{vendor}/{package}/{version}/{pathToAsset}` to append to your CDN url. That'll mean if your CDN domain is set
to `https://cdn.foo.com` and you want to show get `/css/app.css` for the `foo/bar` package, which is installed with
version 5.0, you will end up with: `https://cdn.foo.com/foo/bar/5.0/css/app.css`.

The format may be changed via the `mix.driver.cdn.format` config option. So if you wanted to omit the vendor you could
set this to:

```php
// config/mix.php
return [
    'driver' => [
        'cdn' => [
            'format' => '{url}/{package}/{version}/{path}'
        ]
    ]   
];
``` 

Then it will read the URL as `https://cdn.foo.com/bar/5.0/css/app.css`.