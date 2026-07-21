import { page } from '@inertiajs/svelte';

/**
 * Base URL for files in public/ (trailing slash).
 * Prefers the server-shared path (matches current request / XAMPP subdirectory);
 * falls back to Vite BASE_URL with /build/ stripped.
 */
export function resolvePublicBase(): string {
    const fromPage = page.props.publicBase;

    if (typeof fromPage === 'string' && fromPage !== '') {
        return fromPage.endsWith('/') ? fromPage : `${fromPage}/`;
    }

    return import.meta.env.BASE_URL.replace(/\/build\/?$/, '/');
}
