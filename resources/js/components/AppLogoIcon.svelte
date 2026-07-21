<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { resolvePublicBase } from '@/lib/publicBase';

    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();

    const logoSrc = $derived.by(() => {
        // Odczyt page.props w derived — ścieżka z serwera ma pierwszeństwo przed Vite BASE_URL.
        void page.props.publicBase;

        return `${resolvePublicBase()}images/logo_ekstraklasa.png`;
    });
</script>

<img
    src={logoSrc}
    alt="BOŚ Bank Ekstraklasa"
    class="object-contain {className}"
    {...rest}
/>
