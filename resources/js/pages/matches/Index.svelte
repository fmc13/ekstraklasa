<script module lang="ts">
    import { index } from '@/routes/matches';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Mecze',
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

    type MatchFixture = {
        id: number;
        api_fixture_id: number;
        kickoff_at: string | null;
        status_short: string | null;
        status_long: string | null;
        home_team_id: number;
        home_team_name: string;
        home_team_logo: string | null;
        away_team_id: number;
        away_team_name: string;
        away_team_logo: string | null;
        home_goals: number | null;
        away_goals: number | null;
        venue_name: string | null;
        venue_city: string | null;
    };

    type MatchRound = {
        name: string;
        number: number | null;
        fixtures: MatchFixture[];
    };

    type LeagueInfo = {
        id: number;
        name: string;
        season: number;
    };

    let {
        rounds = [],
        currentRound = null,
        league,
    }: {
        rounds?: MatchRound[];
        currentRound?: number | null;
        league: LeagueInfo;
    } = $props();

    let selectedRound = $state<number | 'all' | null>(null);

    const roundOptions = $derived(
        rounds
            .map((round) => round.number)
            .filter((value): value is number => value !== null)
            .filter((value, index, all) => all.indexOf(value) === index)
            .sort((a, b) => a - b),
    );

    const activeRound = $derived.by((): number | 'all' => {
        if (selectedRound !== null) {
            return selectedRound;
        }

        if (currentRound !== null && roundOptions.includes(currentRound)) {
            return currentRound;
        }

        return roundOptions[0] ?? 'all';
    });

    const visibleRounds = $derived(
        activeRound === 'all'
            ? rounds
            : rounds.filter((round) => round.number === activeRound),
    );

    function roundLabel(round: MatchRound): string {
        if (round.number !== null) {
            return `Kolejka ${round.number}`;
        }

        return round.name;
    }

    function formatKickoff(value: string | null): string {
        if (!value) {
            return 'Termin do ustalenia';
        }

        return new Date(value).toLocaleString('pl-PL', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function scoreLabel(fixture: MatchFixture): string {
        if (fixture.home_goals === null || fixture.away_goals === null) {
            return 'vs';
        }

        return `${fixture.home_goals} : ${fixture.away_goals}`;
    }

    function statusLabel(fixture: MatchFixture): string {
        switch (fixture.status_short) {
            case 'FT':
            case 'AET':
            case 'PEN':
                return 'Zakończony';
            case '1H':
            case '2H':
            case 'HT':
            case 'ET':
            case 'BT':
            case 'P':
            case 'LIVE':
                return 'Na żywo';
            case 'NS':
                return 'Zaplanowany';
            case 'PST':
                return 'Przełożony';
            case 'CANC':
                return 'Odwołany';
            case 'TBD':
                return 'Do ustalenia';
            default:
                return fixture.status_long ?? fixture.status_short ?? '—';
        }
    }
</script>

<AppHead title="Mecze" />

<div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
    <Heading
        title="Mecze"
        description="Kolejki {league.name} w sezonie {league.season}/{league.season + 1}."
    />

    {#if rounds.length > 0}
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                class="rounded-full border px-3 py-1.5 text-sm transition {activeRound === 'all'
                    ? 'border-primary bg-primary text-primary-foreground'
                    : 'border-border hover:bg-accent'}"
                onclick={() => (selectedRound = 'all')}
            >
                Wszystkie
            </button>
            {#each roundOptions as roundNumber (roundNumber)}
                <button
                    type="button"
                    class="rounded-full border px-3 py-1.5 text-sm transition {activeRound ===
                    roundNumber
                        ? 'border-primary bg-primary text-primary-foreground'
                        : 'border-border hover:bg-accent'}"
                    onclick={() => (selectedRound = roundNumber)}
                >
                    Kolejka {roundNumber}
                </button>
            {/each}
        </div>

        <div class="space-y-8">
            {#each visibleRounds as round (round.name)}
                <section class="space-y-3">
                    <h2 class="text-lg font-semibold tracking-tight">{roundLabel(round)}</h2>
                    <div class="space-y-3">
                        {#each round.fixtures as fixture (fixture.id)}
                            <article
                                class="rounded-xl border border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border"
                            >
                                <div
                                    class="mb-3 flex flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground"
                                >
                                    <span>{formatKickoff(fixture.kickoff_at)}</span>
                                    <span>{statusLabel(fixture)}</span>
                                </div>

                                <div
                                    class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 sm:gap-4"
                                >
                                    <Link
                                        href={toUrl(teamShow(fixture.home_team_id))}
                                        class="flex items-center justify-end gap-2 text-right hover:text-primary"
                                        prefetch
                                    >
                                        <span class="truncate font-medium"
                                            >{fixture.home_team_name}</span
                                        >
                                        {#if fixture.home_team_logo}
                                            <img
                                                src={fixture.home_team_logo}
                                                alt=""
                                                class="size-8 object-contain"
                                                loading="lazy"
                                            />
                                        {/if}
                                    </Link>

                                    <div
                                        class="min-w-14 text-center text-lg font-semibold tabular-nums"
                                    >
                                        {scoreLabel(fixture)}
                                    </div>

                                    <Link
                                        href={toUrl(teamShow(fixture.away_team_id))}
                                        class="flex items-center justify-start gap-2 hover:text-primary"
                                        prefetch
                                    >
                                        {#if fixture.away_team_logo}
                                            <img
                                                src={fixture.away_team_logo}
                                                alt=""
                                                class="size-8 object-contain"
                                                loading="lazy"
                                            />
                                        {/if}
                                        <span class="truncate font-medium"
                                            >{fixture.away_team_name}</span
                                        >
                                    </Link>
                                </div>

                                {#if fixture.venue_name || fixture.venue_city}
                                    <p class="mt-3 text-center text-xs text-muted-foreground">
                                        {[fixture.venue_name, fixture.venue_city]
                                            .filter(Boolean)
                                            .join(' · ')}
                                    </p>
                                {/if}
                            </article>
                        {/each}
                    </div>
                </section>
            {/each}
        </div>
    {:else}
        <p
            class="rounded-xl border border-sidebar-border/70 px-4 py-8 text-center text-sm text-muted-foreground dark:border-sidebar-border"
        >
            Brak meczów. Uruchom synchronizację:
            <code class="mx-1 rounded bg-muted px-1.5 py-0.5 text-xs"
                >php artisan football:sync-fixtures</code
            >
        </p>
    {/if}
</div>
