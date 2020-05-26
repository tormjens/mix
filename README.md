# Extended Mix

Extended Mix was born in an application that was entirely modular. We needed to load assets from our modules (Composer 
packages) while also making it possible to hot reload and host assets on a CDN.

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

## Proxied local assets

This package provides a convenient way of proxying assets stored locally via a route named `mix.show`. While this is 
very neat when testing locally, it is not ideal for production as it bootstraps the entire application just to serve a 
asset. You should consider adding a rule in your webserver that mimmicks this functionality.

## CDN Assets

CDN is supported out of the box, but a convention is necessary to correctly display your assets. By default, we use the
format `/{vendor}/{package}/{version}/{pathToAsset}` to append to your CDN url. That'll mean if your CDN domain is set 
to `https://cdn.foo.com` and you want to show get `/css/app.css` for the `foo/bar` package, which is installed with 
version 5.0, you will end up with: `https://cdn.foo.com/foo/bar/5.0/css/app.css`.

Everything here is done magically, though. So long as you follow the configured format.