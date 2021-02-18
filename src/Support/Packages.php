<?php

namespace TorMorten\Mix\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class Packages
{
    protected $packages;

    protected function prepare()
    {
        if (!$this->packages) {
            $packages = json_decode(
                file_get_contents(
                    rtrim(Config::get('mix.home'), '/') . '/vendor/composer/installed.json'
                ),
                true
            );

            if (isset($packages['packages'])) {
                $packages = $packages['packages'];
            }
            $this->packages = new Collection($packages);
        }
    }

    public function __call($name, $arguments)
    {
        $this->prepare();
        return $this->packages->{$name}(...$arguments);
    }


}