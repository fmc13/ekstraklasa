<?php

namespace App\Support;

final class PublicPath
{
    /**
     * Root-relative base for files in public/ (trailing slash).
     *
     * Prefer pathname from APP_URL so XAMPP subdirectory installs
     * (e.g. /ekstraklasa/public) keep working even when the request
     * base path is not detected. Domain-root installs (production,
     * artisan serve) resolve to "/".
     */
    public static function base(?string $appUrl = null): string
    {
        $appUrl ??= config('app.url');

        if (is_string($appUrl) && $appUrl !== '') {
            $pathname = parse_url($appUrl, PHP_URL_PATH);

            if (is_string($pathname)) {
                $pathname = rtrim($pathname, '/');

                if ($pathname !== '') {
                    return $pathname.'/';
                }
            }
        }

        $assetPath = parse_url(asset('images/logo_ekstraklasa.png'), PHP_URL_PATH);

        if (! is_string($assetPath) || $assetPath === '') {
            return '/';
        }

        $imagesDir = dirname($assetPath);
        $base = dirname($imagesDir);
        $normalized = str_replace('\\', '/', $base);

        if ($normalized === '/' || $normalized === '.') {
            return '/';
        }

        return rtrim($normalized, '/').'/';
    }

    /**
     * Root-relative path to a file under public/.
     */
    public static function to(string $relative, ?string $appUrl = null): string
    {
        return self::base($appUrl).ltrim($relative, '/');
    }
}
