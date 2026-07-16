<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex');

echo "PHP: ".PHP_VERSION.PHP_EOL;
echo "SAPI: ".PHP_SAPI.PHP_EOL;
echo "cwd: ".getcwd().PHP_EOL;
echo "script: ".__FILE__.PHP_EOL;

$base = dirname(__DIR__);
echo "base: {$base}".PHP_EOL;

$envPath = $base.'/.env';
echo ".env exists: ".(is_file($envPath) ? 'yes' : 'no').PHP_EOL;
echo ".env readable: ".(is_readable($envPath) ? 'yes' : 'no').PHP_EOL;

if (is_readable($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES) ?: [];
    foreach ($lines as $line) {
        if (str_starts_with($line, 'APP_') || str_starts_with($line, 'DB_') || str_starts_with($line, 'SESSION_') || str_starts_with($line, 'CACHE_')) {
            if (str_contains($line, 'KEY=') || str_contains($line, 'PASSWORD=')) {
                [$k] = explode('=', $line, 2);
                echo $k.'=(redacted)'.PHP_EOL;
            } else {
                echo $line.PHP_EOL;
            }
        }
    }
}

$sqlite = $base.'/database/database.sqlite';
echo "sqlite exists: ".(is_file($sqlite) ? 'yes' : 'no').PHP_EOL;
echo "sqlite writable: ".(is_writable($sqlite) ? 'yes' : 'no').PHP_EOL;
echo "database dir writable: ".(is_writable(dirname($sqlite)) ? 'yes' : 'no').PHP_EOL;

foreach ([
    'storage',
    'storage/framework',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
] as $dir) {
    $path = $base.'/'.$dir;
    echo $dir.': '.(is_dir($path) ? 'dir' : 'MISSING')
        .' writable='.(is_writable($path) ? 'yes' : 'no').PHP_EOL;
}

$configCache = $base.'/bootstrap/cache/config.php';
echo "config cache: ".(is_file($configCache) ? 'YES' : 'no').PHP_EOL;

$autoload = $base.'/vendor/autoload.php';
echo "autoload: ".(is_file($autoload) ? 'yes' : 'no').PHP_EOL;

if (is_file($autoload)) {
    try {
        require $autoload;
        $app = require $base.'/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        echo "laravel boot: ok".PHP_EOL;
        echo "app.env: ".$app->environment().PHP_EOL;
        echo "app.debug: ".($app->hasDebugModeEnabled() ? 'true' : 'false').PHP_EOL;
        echo "app.key set: ".(filled(config('app.key')) ? 'yes' : 'no').PHP_EOL;
        echo "session.driver: ".config('session.driver').PHP_EOL;
        echo "cache.default: ".config('cache.default').PHP_EOL;
        echo "db.default: ".config('database.default').PHP_EOL;
    } catch (Throwable $e) {
        echo "laravel boot error: ".get_class($e).': '.$e->getMessage().PHP_EOL;
        echo $e->getFile().':'.$e->getLine().PHP_EOL;
    }
}

try {
    $request = Illuminate\Http\Request::create('/', 'GET');
    $response = $kernel->handle($request);
    echo "home status: ".$response->getStatusCode().PHP_EOL;
    if ($response->getStatusCode() >= 400) {
        echo "home body snippet: ".substr(strip_tags($response->getContent()), 0, 500).PHP_EOL;
    }
    $kernel->terminate($request, $response);
} catch (Throwable $e) {
    echo "home error: ".get_class($e).': '.$e->getMessage().PHP_EOL;
    echo $e->getFile().':'.$e->getLine().PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
}
