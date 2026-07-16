<script lang="ts">
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();

    // Plik leży w public/images — zawsze od roota domeny (nie od Vite base / subdirectory).
    const logoSrc = '/images/logo_ekstraklasa.png';
</script>

<img
    src={logoSrc}
    alt="BOŚ Bank Ekstraklasa"
    class="object-contain {className}"
    {...rest}
/>
