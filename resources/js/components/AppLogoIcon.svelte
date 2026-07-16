<script lang="ts">
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();

    const logoSrc = `${import.meta.env.BASE_URL}images/ekstraklasa-logo.png`;
</script>

<img
    src={logoSrc}
    alt="PKO Bank Polski Ekstraklasa"
    class="object-contain {className}"
    {...rest}
/>
