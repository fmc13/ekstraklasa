<script lang="ts">
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();

    // Vite BASE_URL ends with /build/ — strip it so local XAMPP subdirectory
    // and production domain root both resolve public/images correctly.
    const publicBase = import.meta.env.BASE_URL.replace(/\/build\/?$/, '/');
    const logoSrc = `${publicBase}images/logo_ekstraklasa.png`;
</script>

<img
    src={logoSrc}
    alt="BOŚ Bank Ekstraklasa"
    class="object-contain {className}"
    {...rest}
/>
