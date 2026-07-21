<?php

namespace App\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

final class RootUrlConfigurator
{
    public function __construct(private Application $app) {}

    /**
     * Production: force the canonical APP_URL.
     * Local subdirectory (XAMPP): keep APP_URL path, but use the live
     * request host so LAN access still loads assets.
     * Local root (artisan serve): do not force — follow the request.
     */
    public function configure(?string $appUrl, ?Request $request = null): void
    {
        if (! is_string($appUrl) || $appUrl === '') {
            return;
        }

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
            URL::forceRootUrl($appUrl);

            return;
        }

        $path = rtrim((string) parse_url($appUrl, PHP_URL_PATH), '/');

        if ($path === '') {
            // artisan serve / instalacja w katalogu głównym — czyść ewentualny
            // wcześniejszy force, żeby generator brał host z requestu.
            URL::forceRootUrl(null);

            return;
        }

        if ($request instanceof Request && filled($request->getHost())) {
            URL::forceRootUrl($request->getSchemeAndHttpHost().$path);

            return;
        }

        URL::forceRootUrl($appUrl);
    }
}
