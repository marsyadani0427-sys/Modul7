<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */

$app = require_once __DIR__.'/../bootstrap/app.php';

// DIAGNOSTIK SEMENTARA
$servicesPath = $app->getCachedServicesPath();

error_log('[LARAVEL_DIAG] services_path='.$servicesPath);

error_log('[LARAVEL_DIAG] services_file_exists='.
    (is_file($servicesPath) ? 'yes' : 'no'));

error_log('[LARAVEL_DIAG] view_bound='.
    ($app->bound('view') ? 'yes' : 'no'));

error_log('[LARAVEL_DIAG] view_deferred='.
    ($app->isDeferredService('view') ? 'yes' : 'no'));

error_log('[LARAVEL_DIAG] view_provider_loaded='.
    ($app->providerIsLoaded(
        \Illuminate\View\ViewServiceProvider::class
    ) ? 'yes' : 'no'));

// JALANKAN APLIKASI
$app->handleRequest(Request::capture());

