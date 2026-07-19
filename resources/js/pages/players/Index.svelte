<script module lang="ts">
    import { index } from '@/routes/players';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Zawodnicy',
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
    import { index as playersIndex, show as playerShow } from '@/routes/players';

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
        letter,
        letters = [],
        players = [],
        league,
    }: {
        letter: string;
        letters?: string[];
        players?: PlayerCard[];
        league: LeagueInfo;
    } = $props();

    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

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

    function letterUrl(value: string): string {
        return toUrl(
            playersIndex({
                query: {
                    letter: value,
                },
            }),
        );
    }
</script>

<AppHead title="Zawodnicy" />

<div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
    <Heading
        title="Zawodnicy"
        description="Składy klubów {league.name} w sezonie {league.season}/{league.season + 1}."
    />

    {#if letters.length > 0}
        <div class="flex flex-wrap items-center gap-2">
            {#each alphabet as alphabetLetter (alphabetLetter)}
                {@const available = letters.includes(alphabetLetter)}
                {#if available}
                    <Link
                        href={letterUrl(alphabetLetter)}
                        class="flex size-9 items-center justify-center rounded-full border text-sm font-medium transition {letter ===
                        alphabetLetter
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border hover:bg-accent'}"
                        prefetch
                    >
                        {alphabetLetter}
                    </Link>
                {:else}
                    <span
                        class="flex size-9 items-center justify-center rounded-full border border-transparent text-sm text-muted-foreground/40"
                        aria-disabled="true"
                    >
                        {alphabetLetter}
                    </span>
                {/if}
            {/each}
        </div>

        {#if players.length > 0}
            <div
                class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                {#each players as player (player.api_player_id)}
                    <Link
                        href={toUrl(playerShow(player.api_player_id))}
                        class="flex items-center gap-3 rounded-xl border border-sidebar-border/70 px-3 py-3 transition-colors hover:border-primary/40 hover:bg-accent/60 focus-visible:ring-2 focus-visible:ring-ring dark:border-sidebar-border"
                        prefetch
                    >
                        {#if player.photo}
                            <img
                                src={player.photo}
                                alt=""
                                class="size-14 shrink-0 rounded-full object-cover"
                                loading="lazy"
                            />
                        {:else}
                            <div
                                class="flex size-14 shrink-0 items-center justify-center rounded-full bg-muted text-lg font-semibold text-muted-foreground"
                            >
                                {player.name.slice(0, 1)}
                            </div>
                        {/if}

                        <div class="min-w-0 flex-1">
                            <div class="truncate font-medium">{player.name}</div>
                            <div class="mt-0.5 text-sm text-muted-foreground">
                                {positionLabel(player.position)}
                            </div>
                        </div>

                        {#if player.team.logo}
                            <img
                                src={player.team.logo}
                                alt={player.team.name ?? ''}
                                class="size-8 shrink-0 object-contain"
                                loading="lazy"
                                title={player.team.name ?? undefined}
                            />
                        {:else if player.team.name}
                            <span
                                class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-semibold text-muted-foreground"
                                title={player.team.name}
                            >
                                {player.team.name.slice(0, 1)}
                            </span>
                        {/if}
                    </Link>
                {/each}
            </div>
        {:else}
            <p
                class="rounded-xl border border-sidebar-border/70 px-4 py-8 text-center text-sm text-muted-foreground dark:border-sidebar-border"
            >
                Brak zawodników na literę {letter}.
            </p>
        {/if}
    {:else}
        <p
            class="rounded-xl border border-sidebar-border/70 px-4 py-8 text-center text-sm text-muted-foreground dark:border-sidebar-border"
        >
            Brak zawodników. Uruchom synchronizację:
            <code class="mx-1 rounded bg-muted px-1.5 py-0.5 text-xs"
                >php artisan football:sync-squads</code
            >
        </p>
    {/if}
</div>
