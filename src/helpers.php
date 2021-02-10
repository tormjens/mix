<?php

function extendedMix($filename, $package, $forceLocal = false)
{
    return resolve(\TorMorten\Mix\Mix::class)->handle($filename, $package, $forceLocal);
}

function variableAsset($filename, $package)
{
    return resolve(\TorMorten\Mix\VariableAsset::class)->handle($filename, $package);
}