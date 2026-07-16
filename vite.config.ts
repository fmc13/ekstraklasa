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

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    // VITE_BASE ma pierwszeństwo. W przeciwnym razie bierzemy pathname z APP_URL
    // tylko dla lokalnych instalacji w podkatalogu (np. /ekstraklasa/public).
    // Na produkcji (https://ekstraklasa.9liga.waw.pl) pathname to "/" → base "/".
    let base = '/';

    if (env.VITE_BASE) {
        base = env.VITE_BASE.endsWith('/') ? env.VITE_BASE : `${env.VITE_BASE}/`;
    } else if (env.APP_URL) {
        const pathname = new URL(env.APP_URL).pathname.replace(/\/$/, '');

        if (pathname !== '') {
            base = `${pathname}/`;
        }
    }

    return {
        base,
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
