<?php

use Sitroz\LaraBridge\LaraBridge;

if (defined('LARAVEL_USED') && LARAVEL_USED) {
    return TRUE;
}

define('LARAVEL_START', microtime(true));

const LARAVEL_USED = TRUE;

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/app.php';

if (class_exists(LaraBridge::class) && $bridge = app(LaraBridge::BOUND_KEY)){
    // dev
    /** @var LaraBridge $bridge */
    list($request, $response) = $bridge->handleHttpRequest();

    // master
    // list($request, $response) = app(LaraBridge::BOUND_KEY)->handleHttpRequest();
}
