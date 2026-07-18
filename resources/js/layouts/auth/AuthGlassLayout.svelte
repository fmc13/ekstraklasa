<script lang="ts">
    import type { Snippet } from 'svelte';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    // Vite BASE_URL ends with /build/ — strip it to reach public/ on both
    // local subdirectory installs and production domain roots.
    const publicBase = import.meta.env.BASE_URL.replace(/\/build\/?$/, '/');
    const stadiumSrc = `${publicBase}images/stadium-bg.jpg`;
</script>

<div
    class="relative flex min-h-svh items-center justify-center overflow-hidden bg-emerald-950 p-4 sm:p-6"
>
    <div
        class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('{stadiumSrc}')"
        role="presentation"
    ></div>
    <div
        class="absolute inset-0 bg-linear-to-br from-primary/85 via-emerald-950/80 to-black/75"
        role="presentation"
    ></div>
    <div class="relative z-10 w-full max-w-md">
        {@render children?.()}
    </div>
</div>
