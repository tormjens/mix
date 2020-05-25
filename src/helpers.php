<?php

function extendedMix($filename, $package)
{
    return resolve(\TorMorten\Mix\Mix::class)->handle($filename, $package);
}