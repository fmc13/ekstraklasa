<script module lang="ts">
    import { index } from '@/routes/teams';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Kluby',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { toUrl } from '@/lib/utils';
    import { show as teamShow } from '@/routes/teams';

    type ClubTile = {
        api_team_id: number;
        name: string;
        logo: string | null;
    };

    type LeagueInfo = {
        id: number;
        name: string;
        season: number;
    };

    let {
        teams = [],
        league,
    }: {
        teams?: ClubTile[];
        league: LeagueInfo;
    } = $props();
</script>

<AppHead title="Kluby" />

<div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
    <Heading
        title="Kluby"
        description="Kluby {league.name} w sezonie {league.season}/{league.season + 1}."
    />

    {#if teams.length > 0}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
            {#each teams as club (club.api_team_id)}
                <Link
                    href={toUrl(teamShow(club.api_team_id))}
                    class="flex flex-col items-center gap-3 rounded-xl border border-sidebar-border/70 p-4 text-center transition-colors hover:border-primary/40 hover:bg-accent/60 focus-visible:ring-2 focus-visible:ring-ring dark:border-sidebar-border"
                    prefetch
                >
                    <div class="flex size-16 items-center justify-center sm:size-20">
                        {#if club.logo}
                            <img
                                src={club.logo}
                                alt=""
                                class="max-h-full max-w-full object-contain"
                                loading="lazy"
                            />
                        {:else}
                            <span
                                class="flex size-full items-center justify-center rounded-full bg-muted text-lg font-semibold text-muted-foreground"
                            >
                                {club.name.slice(0, 1)}
                            </span>
                        {/if}
                    </div>
                    <span class="line-clamp-2 text-sm font-medium leading-snug">
                        {club.name}
                    </span>
                </Link>
            {/each}
        </div>
    {:else}
        <p class="rounded-xl border border-sidebar-border/70 px-4 py-8 text-center text-sm text-muted-foreground dark:border-sidebar-border">
            Brak klubów. Uruchom synchronizację:
            <code class="mx-1 rounded bg-muted px-1.5 py-0.5 text-xs"
                >php artisan football:sync-teams</code
            >
        </p>
    {/if}
</div>
