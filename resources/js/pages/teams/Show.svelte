<script module lang="ts">
    import { index as teamsIndex } from '@/routes/teams';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Kluby',
                href: teamsIndex(),
            },
            {
                title: 'Zespół',
                href: teamsIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, setLayoutProps } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { toUrl } from '@/lib/utils';
    import { show as teamShow } from '@/routes/teams';

    type TeamVenue = {
        name: string | null;
        address: string | null;
        city: string | null;
        capacity: number | null;
        surface: string | null;
        image: string | null;
    };

    type TeamInfo = {
        api_team_id: number;
        name: string;
        code: string | null;
        country: string | null;
        founded: number | null;
        national: boolean;
        logo: string | null;
        venue: TeamVenue;
    };

    type StandingInfo = {
        rank: number;
        played: number;
        win: number;
        draw: number;
        lose: number;
        goals_for: number;
        goals_against: number;
        goals_diff: number;
        points: number;
        form: string | null;
        description: string | null;
    };

    type PlayerInfo = {
        api_player_id: number;
        name: string;
        age: number | null;
        number: number | null;
        position: string | null;
        photo: string | null;
    };

    type CoachInfo = {
        api_coach_id: number;
        name: string;
        firstname: string | null;
        lastname: string | null;
        age: number | null;
        nationality: string | null;
        photo: string | null;
    };

    type LeagueInfo = {
        id: number;
        name: string;
        season: number;
    };

    let {
        team,
        standing = null,
        players = [],
        coaches = [],
        league,
    }: {
        team: TeamInfo;
        standing?: StandingInfo | null;
        players?: PlayerInfo[];
        coaches?: CoachInfo[];
        league: LeagueInfo;
    } = $props();

    $effect(() => {
        setLayoutProps({
            breadcrumbs: [
                {
                    title: 'Kluby',
                    href: teamsIndex(),
                },
                {
                    title: team.name,
                    href: teamShow(team.api_team_id),
                },
            ],
        });
    });

    const hasVenue = $derived(
        Boolean(
            team.venue.name ||
                team.venue.city ||
                team.venue.address ||
                team.venue.capacity ||
                team.venue.surface ||
                team.venue.image,
        ),
    );

    function formResultClass(result: string): string {
        if (result === 'W') {
            return 'bg-emerald-600 text-white';
        }

        if (result === 'D') {
            return 'bg-amber-500 text-white';
        }

        if (result === 'L') {
            return 'bg-rose-600 text-white';
        }

        return 'bg-muted text-muted-foreground';
    }

    function formatGoalsDiff(value: number): string {
        if (value > 0) {
            return `+${value}`;
        }

        return String(value);
    }

    function formatNumber(value: number | null): string {
        if (value === null) {
            return '—';
        }

        return value.toLocaleString('pl-PL');
    }

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

<AppHead title={team.name} />

<div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-4">
            {#if team.logo}
                <img
                    src={team.logo}
                    alt=""
                    class="size-16 object-contain sm:size-20"
                    loading="lazy"
                />
            {/if}
            <Heading
                title={team.name}
                description="{league.name} {league.season}/{league.season + 1}"
            />
        </div>
        <Link
            href={toUrl(teamsIndex())}
            class="text-sm font-medium text-primary hover:underline"
        >
            ← Wróć do klubów
        </Link>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="space-y-4">
            <h2 class="text-lg font-semibold tracking-tight">Informacje o klubie</h2>
            <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 text-sm">
                <dt class="text-muted-foreground">Kod</dt>
                <dd class="font-medium">{team.code ?? '—'}</dd>
                <dt class="text-muted-foreground">Kraj</dt>
                <dd class="font-medium">{team.country ?? '—'}</dd>
                <dt class="text-muted-foreground">Rok założenia</dt>
                <dd class="font-medium">{team.founded ?? '—'}</dd>
                <dt class="text-muted-foreground">Typ</dt>
                <dd class="font-medium">{team.national ? 'Reprezentacja' : 'Klub'}</dd>
            </dl>
        </section>

        <section class="space-y-4">
            <h2 class="text-lg font-semibold tracking-tight">Stadion</h2>
            {#if hasVenue}
                <div class="flex flex-col gap-4 sm:flex-row">
                    {#if team.venue.image}
                        <img
                            src={team.venue.image}
                            alt={team.venue.name ?? 'Stadion'}
                            class="h-32 w-full max-w-xs object-cover sm:h-40"
                            loading="lazy"
                        />
                    {/if}
                    <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 text-sm">
                        <dt class="text-muted-foreground">Nazwa</dt>
                        <dd class="font-medium">{team.venue.name ?? '—'}</dd>
                        <dt class="text-muted-foreground">Miasto</dt>
                        <dd class="font-medium">{team.venue.city ?? '—'}</dd>
                        <dt class="text-muted-foreground">Adres</dt>
                        <dd class="font-medium">{team.venue.address ?? '—'}</dd>
                        <dt class="text-muted-foreground">Pojemność</dt>
                        <dd class="font-medium">{formatNumber(team.venue.capacity)}</dd>
                        <dt class="text-muted-foreground">Nawierzchnia</dt>
                        <dd class="font-medium">{team.venue.surface ?? '—'}</dd>
                    </dl>
                </div>
            {:else}
                <p class="text-sm text-muted-foreground">
                    Brak danych o stadionie. Uruchom synchronizację:
                    <code class="mx-1 rounded bg-muted px-1.5 py-0.5 text-xs"
                        >php artisan football:sync-teams</code
                    >
                </p>
            {/if}
        </section>
    </div>

    {#if standing}
        <section class="space-y-4">
            <h2 class="text-lg font-semibold tracking-tight">
                Sezon {league.season}/{league.season + 1}
            </h2>
            {#if standing.description}
                <p class="text-sm text-muted-foreground">{standing.description}</p>
            {/if}
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="border-b bg-muted/50 text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-medium">#</th>
                            <th class="px-3 py-3 text-center font-medium">M</th>
                            <th class="px-3 py-3 text-center font-medium">Z</th>
                            <th class="px-3 py-3 text-center font-medium">R</th>
                            <th class="px-3 py-3 text-center font-medium">P</th>
                            <th class="px-3 py-3 text-center font-medium">Bramki</th>
                            <th class="px-3 py-3 text-center font-medium">+/−</th>
                            <th class="px-3 py-3 text-center font-medium">Pkt</th>
                            <th class="px-4 py-3 font-medium">Forma</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 py-3 font-medium tabular-nums">{standing.rank}</td>
                            <td class="px-3 py-3 text-center tabular-nums">{standing.played}</td>
                            <td class="px-3 py-3 text-center tabular-nums">{standing.win}</td>
                            <td class="px-3 py-3 text-center tabular-nums">{standing.draw}</td>
                            <td class="px-3 py-3 text-center tabular-nums">{standing.lose}</td>
                            <td class="px-3 py-3 text-center tabular-nums">
                                {standing.goals_for}:{standing.goals_against}
                            </td>
                            <td class="px-3 py-3 text-center tabular-nums">
                                {formatGoalsDiff(standing.goals_diff)}
                            </td>
                            <td class="px-3 py-3 text-center font-semibold tabular-nums">
                                {standing.points}
                            </td>
                            <td class="px-4 py-3">
                                {#if standing.form}
                                    <div class="flex items-center gap-1">
                                        {#each standing.form.split('') as result, index (`form-${index}-${result}`)}
                                            <span
                                                class={`inline-flex size-5 items-center justify-center rounded text-[10px] font-semibold ${formResultClass(result)}`}
                                            >
                                                {result}
                                            </span>
                                        {/each}
                                    </div>
                                {:else}
                                    <span class="text-muted-foreground">—</span>
                                {/if}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    {/if}

    <section class="space-y-4">
        <h2 class="text-lg font-semibold tracking-tight">Sztab szkoleniowy</h2>
        {#if coaches.length > 0}
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {#each coaches as coach (coach.api_coach_id)}
                    <div
                        class="flex items-center gap-3 rounded-xl border border-sidebar-border/70 p-3 dark:border-sidebar-border"
                    >
                        {#if coach.photo}
                            <img
                                src={coach.photo}
                                alt=""
                                class="size-12 rounded-full object-cover"
                                loading="lazy"
                            />
                        {:else}
                            <span
                                class="flex size-12 items-center justify-center rounded-full bg-muted text-sm font-semibold text-muted-foreground"
                            >
                                {coach.name.slice(0, 1)}
                            </span>
                        {/if}
                        <div class="min-w-0">
                            <div class="truncate font-medium">{coach.name}</div>
                            <div class="text-xs text-muted-foreground">
                                Trener
                                {#if coach.nationality}
                                    · {coach.nationality}
                                {/if}
                                {#if coach.age}
                                    · {coach.age} lat
                                {/if}
                            </div>
                        </div>
                    </div>
                {/each}
            </div>
        {:else}
            <p class="text-sm text-muted-foreground">
                Brak danych o trenerze. Uruchom synchronizację:
                <code class="mx-1 rounded bg-muted px-1.5 py-0.5 text-xs"
                    >php artisan football:sync-squads</code
                >
            </p>
        {/if}
    </section>

    <section class="space-y-4">
        <h2 class="text-lg font-semibold tracking-tight">Skład</h2>
        {#if players.length > 0}
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead class="border-b bg-muted/50 text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-medium">#</th>
                            <th class="px-4 py-3 font-medium">Zawodnik</th>
                            <th class="px-4 py-3 font-medium">Pozycja</th>
                            <th class="px-4 py-3 text-center font-medium">Wiek</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each players as player (player.api_player_id)}
                            <tr class="border-b last:border-0">
                                <td class="px-4 py-3 tabular-nums text-muted-foreground">
                                    {player.number ?? '—'}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        {#if player.photo}
                                            <img
                                                src={player.photo}
                                                alt=""
                                                class="size-8 rounded-full object-cover"
                                                loading="lazy"
                                            />
                                        {/if}
                                        <span class="font-medium">{player.name}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {positionLabel(player.position)}
                                </td>
                                <td class="px-4 py-3 text-center tabular-nums">
                                    {player.age ?? '—'}
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {:else}
            <p class="text-sm text-muted-foreground">
                Brak danych o składzie. Uruchom synchronizację:
                <code class="mx-1 rounded bg-muted px-1.5 py-0.5 text-xs"
                    >php artisan football:sync-squads</code
                >
            </p>
        {/if}
    </section>
</div>
