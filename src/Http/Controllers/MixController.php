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
        $path = str_replace(["$vendor/", $package], '', $path);

        if ($local->exists($params = ['package' => join('/', [$vendor, $package]), 'filename' => $path])) {
            $file = $local->inManifest($params);
            $path = $local->getFilePath($params, $path);
            $mime = mime_content_type($path);

            return Response::make(
                file_get_contents($path),
                200,
                ['Content-type' => $mime]
            );
        } else {
            return new NotFoundHttpException('File not found.');
        }
    }
}