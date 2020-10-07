<?php

namespace TorMorten\MixTests;

use TorMorten\Mix\MixServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            MixServiceProvider::class
        ];
    }
}