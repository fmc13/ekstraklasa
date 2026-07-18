<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import AuthGlassLayout from '@/layouts/auth/AuthGlassLayout.svelte';
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

<AuthGlassLayout>
    <div
        class="rounded-2xl border border-white/25 bg-white/10 px-6 py-8 shadow-2xl backdrop-blur-xl sm:px-8 sm:py-10"
    >
        <div class="mb-8 flex flex-col items-center gap-4 text-center">
            <Link href={home()} class="flex items-center justify-center">
                <AppLogoIcon class="h-12 w-auto max-w-[240px]" />
                <span class="sr-only">{title || 'Ekstraklasa'}</span>
            </Link>
            {#if title}
                <div class="space-y-2">
                    <h1 class="text-2xl font-semibold tracking-tight text-white">
                        {title}
                    </h1>
                    {#if description}
                        <p class="text-sm text-white/75">{description}</p>
                    {/if}
                </div>
            {/if}
        </div>
        <div class="text-white [&_a]:text-white [&_label]:text-white [&_input]:border-white/35 [&_input]:bg-white/10 [&_input]:text-white [&_input]:placeholder:text-white/70">
            {@render children?.()}
        </div>
    </div>
</AuthGlassLayout>
