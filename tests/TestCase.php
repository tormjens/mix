<?php

namespace TorMorten\MixTests;

use TorMorten\Mix\MixServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        config([
            'mix.run_in_tests' => true,
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            MixServiceProvider::class
        ];
    }
}