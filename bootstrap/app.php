<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->statefulApi();
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e): void {
            error_log(
                'LARAVEL_ROOT_EXCEPTION: ' .
                get_class($e) .
                ' | ' . $e->getMessage() .
                ' | ' . $e->getFile() .
                ':' . $e->getLine()
            );
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->has('debug') || config('app.debug')) {
                return response()->json([
                    'status' => 'EXCEPTION_CAUGHT',
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 10),
                ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
        });
    })->create();

if (isset($_ENV['LARAVEL_STORAGE_PATH'])) {
    $app->useStoragePath($_ENV['LARAVEL_STORAGE_PATH']);
}

return $app;
