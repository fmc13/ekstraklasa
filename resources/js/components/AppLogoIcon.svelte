<script lang="ts">
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();

    // Plik leży w public/images — zawsze od roota domeny (nie od Vite base / subdirectory).
    const logoSrc = '/images/ekstraklasa-logo.png';
</script>

<img
    src={logoSrc}
    alt="PKO Bank Polski Ekstraklasa"
    class="object-contain {className}"
    {...rest}
/>
