<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'canManageUsers' => $request->user()?->can('viewAny', User::class) ?? false,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // Ścieżka public/ względem aktualnego requestu (XAMPP w podkatalogu,
            // artisan serve na /, dostęp po IP w LAN) — niezależna od Vite BASE_URL.
            'publicBase' => $this->publicBase(),
        ];
    }

    /**
     * Root-relative base for files in public/ (trailing slash).
     */
    private function publicBase(): string
    {
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
}
