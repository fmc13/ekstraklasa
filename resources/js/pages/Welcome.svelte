<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard, login } from '@/routes';

    const auth = $derived(page.props.auth);
</script>

<AppHead title="Welcome" />

<div
    class="flex min-h-screen flex-col items-center bg-white p-6 text-black lg:justify-center lg:p-8 dark:bg-black dark:text-white"
>
    <header class="mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
        <nav class="flex items-center justify-between gap-4">
            <Link href="/" class="flex items-center">
                <AppLogoIcon class="h-10 w-auto max-w-[220px]" />
            </Link>
            <div class="flex items-center gap-3">
                {#if auth.user}
                    <Link
                        href={toUrl(dashboard())}
                        class="inline-block rounded-md border border-black/15 bg-primary px-5 py-1.5 text-sm leading-normal text-white hover:bg-primary/90 dark:border-white/20"
                    >
                        Dashboard
                    </Link>
                {:else}
                    <Link
                        href={toUrl(login())}
                        class="inline-block rounded-md border border-black/15 bg-primary px-5 py-1.5 text-sm leading-normal text-white hover:bg-primary/90 dark:border-white/20"
                    >
                        Zaloguj się
                    </Link>
                {/if}
            </div>
        </nav>
    </header>

    <div
        class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
    >
        <main
            class="flex w-full max-w-[335px] flex-col overflow-hidden rounded-lg border border-black/10 shadow-sm lg:max-w-4xl lg:flex-row dark:border-white/10"
        >
            <div
                class="flex flex-1 flex-col justify-center bg-white p-8 lg:p-16 dark:bg-neutral-950"
            >
                <p
                    class="mb-3 text-xs font-semibold tracking-[0.2em] text-primary uppercase"
                >
                    PKO Bank Polski
                </p>
                <h1 class="mb-3 text-3xl font-bold tracking-tight text-black lg:text-4xl dark:text-white">
                    Ekstraklasa
                </h1>
                <p class="mb-8 max-w-md text-sm leading-relaxed text-neutral-600 dark:text-neutral-400">
                    Oficjalna platforma ligowa. Zaloguj się, aby przejść do
                    panelu i zarządzać kontem.
                </p>
                {#if auth.user}
                    <Link
                        href={toUrl(dashboard())}
                        class="inline-flex w-fit items-center rounded-md bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary/90"
                    >
                        Przejdź do panelu
                    </Link>
                {:else}
                    <Link
                        href={toUrl(login())}
                        class="inline-flex w-fit items-center rounded-md bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary/90"
                    >
                        Zaloguj się
                    </Link>
                {/if}
            </div>
            <div
                class="relative flex aspect-[335/220] w-full items-center justify-center bg-primary lg:aspect-auto lg:w-[420px] lg:min-h-[360px]"
            >
                <AppLogoIcon
                    class="h-16 w-auto max-w-[85%] opacity-100 transition-all duration-750 starting:opacity-0 motion-safe:starting:translate-y-4 lg:h-20"
                />
            </div>
        </main>
    </div>
</div>
