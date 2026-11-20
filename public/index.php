<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// DIAGNOSTIC MODE
if (($_GET['diag'] ?? '') === '1') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        require __DIR__ . '/../vendor/autoload.php';

        /** @var Application $app */
        $app = require __DIR__ . '/../bootstrap/app.php';

        $kernel = $app->make(
            \Illuminate\Contracts\Http\Kernel::class
        );

        $kernel->bootstrap();

        echo json_encode([
            'diagnostic' => 'Laravel Bootstrap Check',
            'laravel_version' => $app->version(),
            'services_path' => $app->getCachedServicesPath(),
            'services_file_exists' => file_exists(
                $app->getCachedServicesPath()
            ),
            'view_bound' => $app->bound('view'),
            'view_provider_loaded' => $app->providerIsLoaded(
                \Illuminate\View\ViewServiceProvider::class
            ),
            'view_resolve_test' => get_class($app->make('view')),
            'status' => 'SUCCESS'
        ], JSON_PRETTY_PRINT);

    } catch (\Throwable $e) {
        http_response_code(500);

        echo json_encode([
            'status' => 'FAILED',
            'error' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], JSON_PRETTY_PRINT);
    }

    exit;
}

// NORMAL LARAVEL REQUEST
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
