<?php

namespace TorMorten\Mix\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TorMorten\Mix\Resolvers\ResolveLocal;

class MixController
{
    public function __invoke($path, ResolveLocal $local)
    {
        [$vendor, $package] = explode('/', $path);

        $path = str_replace("$vendor/$package", '', $path);

        $showResource = function ($local, $params) use ($path) {
            $file = $local->inManifest($params);
            $path = $local->getFilePath($params, $path);

            switch (pathinfo($path, PATHINFO_EXTENSION)) {
                case 'css':
                    $mime = 'text/css';
                    break;
                case 'js':
                    $mime = 'text/javascript';
                    break;
                case 'jpg':
                    $mime = 'image/jpeg';
                    break;
                case 'png':
                    $mime = 'image/png';
                    break;
                default;
                    $mime = mime_content_type($path);
            }

            if (!$mime) {
                $mime = 'text/plain';
            }

            return Response::make(
                file_get_contents($path),
                200,
                ['Content-type' => $mime]
            );
        };

        if ($local->exists($params = ['package' => join('/', [$vendor, $package]), 'filename' => $path])) {
            return $showResource($local, $params);
        } else if ($local->exists($params = ['package' => join('/', [$vendor, $package]), 'filename' => $path, 'forceLocal' => true])) {
            return $showResource($local, $params);
        } else {
            return new NotFoundHttpException('File not found.');
        }
    }
}