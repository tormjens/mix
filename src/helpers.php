<?php

function extendedMix($filename, $package, $forceLocal = false)
{
    return resolve(\TorMorten\Mix\Mix::class)->handle($filename, $package, $forceLocal);
}