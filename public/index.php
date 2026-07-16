<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Tymczasowa diagnostyka fatali na produkcji
register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error === null) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (! in_array($error['type'], $fatalTypes, true)) {
        return;
    }

    if (! isset($_GET['_plain_error'])) {
        return;
    }

    if (! headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8', true, 500);
    }

    echo "FATAL: {$error['message']}\n{$error['file']}:{$error['line']}\n";
});

try {
    // Determine if the application is in maintenance mode...
    if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
        require $maintenance;
    }

    // Register the Composer autoloader...
    require __DIR__.'/../vendor/autoload.php';

    // Bootstrap Laravel and handle the request...
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';

    if (isset($_GET['_boot_info'])) {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'PHP: '.PHP_VERSION."\n";
        echo 'bootstrap: '.__DIR__.'/../bootstrap/app.php'."\n";
        echo 'bootstrap size: '.filesize(__DIR__.'/../bootstrap/app.php')."\n";
        echo 'env exists: '.(is_file(__DIR__.'/../.env') ? 'yes' : 'no')."\n";
        echo 'app debug: '.($app->hasDebugModeEnabled() ? 'true' : 'false')."\n";
        echo 'plain handler present: '.(str_contains((string) file_get_contents(__DIR__.'/../bootstrap/app.php'), '_plain_error') ? 'yes' : 'no')."\n";
        echo 'views writable: '.(is_writable(__DIR__.'/../storage/framework/views') ? 'yes' : 'no')."\n";
        echo 'sessions writable: '.(is_writable(__DIR__.'/../storage/framework/sessions') ? 'yes' : 'no')."\n";
        exit;
    }

    $app->handleRequest(Request::capture());
} catch (Throwable $e) {
    if (isset($_GET['_plain_error']) || isset($_GET['_boot_info'])) {
        if (! headers_sent()) {
            header('Content-Type: text/plain; charset=utf-8', true, 500);
        }
        echo get_class($e).': '.$e->getMessage()."\n".$e->getFile().':'.$e->getLine()."\n";
        exit;
    }

    throw $e;
}
