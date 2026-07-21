import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig, loadEnv } from 'vite';

const isSvelteCheck = process.argv.some((argument) => argument.includes('svelte-check'));

if (isSvelteCheck) {
    process.env.LARAVEL_BYPASS_ENV_CHECK ??= '1';
}

/**
 * Vite `base` musi kończyć się na `/build/`, inaczej @font-face z laravel-vite-plugin
 * generuje url("/assets/...") zamiast url("/build/assets/...") i fonty 404-ują.
 *
 * VITE_BASE ma pierwszeństwo. Lokalny XAMPP w podkatalogu bierze pathname z APP_URL.
 */
function resolveViteBase(env: Record<string, string>): string {
    if (env.VITE_BASE) {
        const value = env.VITE_BASE.endsWith('/') ? env.VITE_BASE : `${env.VITE_BASE}/`;

        return value.endsWith('/build/') ? value : `${value.replace(/\/$/, '')}/build/`;
    }

    if (env.APP_URL) {
        try {
            const pathname = new URL(env.APP_URL).pathname.replace(/\/$/, '');

            if (pathname !== '') {
                return `${pathname}/build/`;
            }
        } catch {
            // ignore invalid APP_URL
        }
    }

    return '/build/';
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const hmrHost = env.VITE_HMR_HOST?.trim();

    return {
        base: resolveViteBase(env),
        server: {
            // Nasłuchuj na wszystkich interfejsach — inaczej JS/CSS (a z nimi logo
            // i tła w komponentach Svelte) nie wczytają się z innego PC w LAN.
            host: true,
            cors: true,
            ...(hmrHost
                ? {
                      hmr: {
                          host: hmrHost,
                      },
                  }
                : {}),
        },
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.ts'],
                refresh: true,
                fonts: [
                    bunny('Instrument Sans', {
                        weights: [400, 500, 600],
                    }),
                ],
            }),
            inertia(),
            tailwindcss(),
            svelte(),
            wayfinder({
                formVariants: true,
            }),
        ],
    };
});
