<?php
header('Content-Type: text/plain; charset=utf-8');
echo "PHP: ".PHP_VERSION."\n";
$base = dirname(__DIR__);
echo "base: $base\n";
echo ".env: ".(is_file("$base/.env") ? 'yes' : 'no')." readable=".(is_readable("$base/.env") ? 'yes' : 'no')."\n";
echo "storage writable: ".(is_writable("$base/storage") ? 'yes' : 'no')."\n";
echo "views writable: ".(is_writable("$base/storage/framework/views") ? 'yes' : 'no')."\n";
echo "sessions writable: ".(is_writable("$base/storage/framework/sessions") ? 'yes' : 'no')."\n";
echo "sqlite writable: ".(is_writable("$base/database/database.sqlite") ? 'yes' : 'no')."\n";
if (is_readable("$base/.env")) {
    foreach (file("$base/.env") as $line) {
        if (str_starts_with($line, 'APP_DEBUG') || str_starts_with($line, 'APP_ENV') || str_starts_with($line, 'SESSION_') || str_starts_with($line, 'CACHE_') || str_starts_with($line, 'DB_CONNECTION') || str_starts_with($line, 'DB_DATABASE')) {
            echo rtrim($line)."\n";
        }
        if (str_starts_with($line, 'APP_KEY=')) {
            echo "APP_KEY=(set)\n";
        }
    }
}
