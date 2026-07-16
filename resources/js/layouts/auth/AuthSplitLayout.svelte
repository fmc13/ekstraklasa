<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { home } from '@/routes';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();
</script>

<div
    class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
>
    <div
        class="relative hidden h-full flex-col bg-primary p-10 text-primary-foreground lg:flex dark:border-r"
    >
        <div class="absolute inset-0 bg-primary"></div>
        <Link href={home()} class="relative z-20 flex items-center">
            <AppLogoIcon class="h-12 w-auto max-w-[280px]" />
        </Link>
        <div class="relative z-20 mt-auto">
            <p class="text-lg font-medium">PKO Bank Polski Ekstraklasa</p>
            <p class="mt-2 text-sm text-primary-foreground/80">
                Oficjalna liga piłkarska.
            </p>
        </div>
    </div>
    <div class="lg:p-8">
        <div
            class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]"
        >
            <div class="flex flex-col space-y-2 text-center">
                {#if title}
                    <h1 class="text-xl font-medium tracking-tight">{title}</h1>
                {/if}
                {#if description}
                    <p class="text-sm text-muted-foreground">{description}</p>
                {/if}
            </div>
            {@render children?.()}
        </div>
    </div>
</div>
