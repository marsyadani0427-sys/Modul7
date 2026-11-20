<?php

/**
 * Vercel Serverless Function Entry Point for Laravel
 */

// Mode diagnostik cepat: buka /?diag=1 di browser
if (isset($_GET['diag'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'OK',
        'php_version' => PHP_VERSION,
        'vercel_env' => [
            'APP_KEY_SET' => !empty(getenv('APP_KEY')),
            'DB_HOST_SET' => !empty(getenv('DB_HOST')),
            'DB_CONNECTION' => getenv('DB_CONNECTION') ?: 'default',
        ],
        'tmp_writable' => is_writable('/tmp'),
        'extensions' => [
            'pdo' => extension_loaded('pdo'),
            'pdo_pgsql' => extension_loaded('pdo_pgsql'),
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// 1. Siapkan direktori di /tmp karena filesystem Vercel sifatnya Read-Only
$tmp = '/tmp';
$storagePath = $tmp . '/storage';
$cachePath = $tmp . '/bootstrap/cache';

$dirs = [
    $storagePath,
    $storagePath . '/app',
    $storagePath . '/app/public',
    $storagePath . '/framework',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/views',
    $storagePath . '/logs',
    $cachePath,
    $tmp . '/views',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// 2. Salin cache bootstrap yang ada ke /tmp jika tersedia
$appCacheDir = dirname(__DIR__) . '/bootstrap/cache';
if (is_dir($appCacheDir)) {
    foreach (glob($appCacheDir . '/*.php') as $file) {
        $dest = $cachePath . '/' . basename($file);
        if (!file_exists($dest)) {
            @copy($file, $dest);
        }
    }
}

// 3. Arahkan semua cache & storage internal Laravel ke /tmp
putenv("LARAVEL_STORAGE_PATH={$storagePath}");
$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;

putenv("VIEW_COMPILED_PATH={$storagePath}/framework/views");
$_ENV['VIEW_COMPILED_PATH'] = "{$storagePath}/framework/views";
$_SERVER['VIEW_COMPILED_PATH'] = "{$storagePath}/framework/views";

putenv("APP_CONFIG_CACHE={$cachePath}/config.php");
$_ENV['APP_CONFIG_CACHE'] = "{$cachePath}/config.php";

putenv("APP_EVENTS_CACHE={$cachePath}/events.php");
$_ENV['APP_EVENTS_CACHE'] = "{$cachePath}/events.php";

putenv("APP_PACKAGES_CACHE={$cachePath}/packages.php");
$_ENV['APP_PACKAGES_CACHE'] = "{$cachePath}/packages.php";

putenv("APP_ROUTES_CACHE={$cachePath}/routes-v7.php");
$_ENV['APP_ROUTES_CACHE'] = "{$cachePath}/routes-v7.php";

putenv("APP_SERVICES_CACHE={$cachePath}/services.php");
$_ENV['APP_SERVICES_CACHE'] = "{$cachePath}/services.php";

// Default fallback untuk serverless jika belum diset di Vercel Dashboard
if (empty($_ENV['APP_KEY']) && empty(getenv('APP_KEY'))) {
    putenv('APP_KEY=base64:oLzIs/9ULJv10ytsTLA7aTk+cniwaq7QSXBPZFjyMVo=');
    $_ENV['APP_KEY'] = 'base64:oLzIs/9ULJv10ytsTLA7aTk+cniwaq7QSXBPZFjyMVo=';
}

if (empty($_ENV['SESSION_DRIVER']) && empty(getenv('SESSION_DRIVER'))) {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = 'cookie';
}

if (empty($_ENV['CACHE_STORE']) && empty(getenv('CACHE_STORE'))) {
    putenv('CACHE_STORE=array');
    $_ENV['CACHE_STORE'] = 'array';
}

if (empty($_ENV['LOG_CHANNEL']) && empty(getenv('LOG_CHANNEL'))) {
    putenv('LOG_CHANNEL=stderr');
    $_ENV['LOG_CHANNEL'] = 'stderr';
}

// 4. Normalisasi SCRIPT_NAME & SCRIPT_FILENAME agar router Laravel membaca path secara akurat
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__) . '/public/index.php';

// 5. Muat dan jalankan Laravel front controller
try {
    require dirname(__DIR__) . '/public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'fatal_error' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}
