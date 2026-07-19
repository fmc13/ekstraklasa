<script module lang="ts">
    import { index as playersIndex } from '@/routes/players';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Zawodnicy',
                href: playersIndex(),
            },
            {
                title: 'Zawodnik',
                href: playersIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, setLayoutProps } from '@inertiajs/svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { toUrl } from '@/lib/utils';
    import { show as playerShow } from '@/routes/players';
    import { show as teamShow } from '@/routes/teams';

    type PlayerTeam = {
        api_team_id: number;
        name: string | null;
        logo: string | null;
    };

    type PlayerCard = {
        api_player_id: number;
        name: string;
        age: number | null;
        number: number | null;
        position: string | null;
        photo: string | null;
        team: PlayerTeam;
    };

    type LeagueInfo = {
        id: number;
        name: string;
        season: number;
    };

    let {
        player,
        widgetApiKey = null,
        league,
    }: {
        player: PlayerCard;
        widgetApiKey?: string | null;
        league: LeagueInfo;
    } = $props();

    $effect(() => {
        setLayoutProps({
            breadcrumbs: [
                {
                    title: 'Zawodnicy',
                    href: playersIndex(),
                },
                {
                    title: player.name,
                    href: playerShow(player.api_player_id),
                },
            ],
        });
    });

    onMount(() => {
        if (document.querySelector('script[data-api-sports-widgets]')) {
            return;
        }

        const script = document.createElement('script');
        script.type = 'module';
        script.src = 'https://widgets.api-sports.io/3.1.0/widgets.js';
        script.dataset.apiSportsWidgets = 'true';
        document.body.appendChild(script);
    });

    function positionLabel(position: string | null): string {
        switch (position) {
            case 'Goalkeeper':
                return 'Bramkarz';
            case 'Defender':
                return 'Obrońca';
            case 'Midfielder':
                return 'Pomocnik';
            case 'Attacker':
                return 'Napastnik';
            default:
                return position ?? '—';
        }
    }
</script>

<AppHead title={player.name} />

<div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-4">
            {#if player.photo}
                <img
                    src={player.photo}
                    alt=""
                    class="size-16 rounded-full object-cover sm:size-20"
                    loading="lazy"
                />
            {/if}
            <div>
                <Heading
                    title={player.name}
                    description="{positionLabel(player.position)} · {league.name} {league.season}/{league.season + 1}"
                />
                {#if player.team.name}
                    <Link
                        href={toUrl(teamShow(player.team.api_team_id))}
                        class="mt-1 inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-primary"
                        prefetch
                    >
                        {#if player.team.logo}
                            <img
                                src={player.team.logo}
                                alt=""
                                class="size-5 object-contain"
                                loading="lazy"
                            />
                        {/if}
                        {player.team.name}
                    </Link>
                {/if}
            </div>
        </div>
        <Link
            href={toUrl(playersIndex())}
            class="text-sm font-medium text-primary hover:underline"
        >
            ← Wróć do zawodników
        </Link>
    </div>

    {#if widgetApiKey}
        {#key player.api_player_id}
            <div
                class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <api-sports-widget
                    data-type="config"
                    data-key={widgetApiKey}
                    data-sport="football"
                    data-theme="white"
                    data-timezone="Europe/Warsaw"
                    data-show-logos="true"
                ></api-sports-widget>

                <api-sports-widget
                    data-type="player"
                    data-player-id={String(player.api_player_id)}
                    data-season={String(league.season)}
                    data-player-statistics="true"
                    data-player-trophies="true"
                    data-player-injuries="true"
                ></api-sports-widget>
            </div>
        {/key}
    {:else}
        <div
            class="rounded-xl border border-sidebar-border/70 px-4 py-6 dark:border-sidebar-border"
        >
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">Pozycja</dt>
                    <dd class="font-medium">{positionLabel(player.position)}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Numer</dt>
                    <dd class="font-medium">{player.number ?? '—'}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Wiek</dt>
                    <dd class="font-medium">{player.age ?? '—'}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Klub</dt>
                    <dd class="font-medium">{player.team.name ?? '—'}</dd>
                </div>
            </dl>
            <p class="mt-4 text-sm text-muted-foreground">
                Pełny profil API-Sports wymaga skonfigurowanego klucza
                <code class="mx-1 rounded bg-muted px-1.5 py-0.5 text-xs">API_FOOTBALL_KEY</code>.
            </p>
        </div>
    {/if}
</div>
