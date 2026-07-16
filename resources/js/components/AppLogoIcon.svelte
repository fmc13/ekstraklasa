<script lang="ts">
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();

    const logoSrc = `${import.meta.env.BASE_URL}images/logo_ekstraklasa.png`;
</script>

<img
    src={logoSrc}
    alt="BOŚ Bank Ekstraklasa"
    class="object-contain {className}"
    {...rest}
/>
