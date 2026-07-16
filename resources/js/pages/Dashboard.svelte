<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Ekstraklasa',
                href: dashboard(),
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

    type StandingRow = {
        id: number;
        api_team_id: number;
        rank: number;
        team_name: string;
        team_logo: string | null;
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

    type LeagueInfo = {
        id: number;
        name: string;
        season: number;
    };

    let {
        standings = [],
        league,
    }: {
        standings?: StandingRow[];
        league: LeagueInfo;
    } = $props();

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
</script>

<AppHead title="Ekstraklasa" />

<div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
    <Heading
        title="{league.name} {league.season}/{league.season + 1}"
        description="Tabela BOŚ Bank Ekstraklasy. Dane z API-Football (sezon bieżący); przed startem rozgrywek skład ligi z zerowymi wynikami."
    />

    <div
        class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
    >
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="border-b bg-muted/50 text-muted-foreground">
                <tr>
                    <th class="px-4 py-3 font-medium">#</th>
                    <th class="px-4 py-3 font-medium">Drużyna</th>
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
                {#each standings as row (row.id)}
                    <tr class="border-b last:border-0 hover:bg-muted/40">
                        <td class="px-4 py-3 font-medium tabular-nums">{row.rank}</td>
                        <td class="px-4 py-3">
                            <Link
                                href={toUrl(teamShow(row.api_team_id))}
                                class="flex items-center gap-3 rounded-md outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                prefetch
                            >
                                {#if row.team_logo}
                                    <img
                                        src={row.team_logo}
                                        alt=""
                                        class="size-6 object-contain"
                                        loading="lazy"
                                    />
                                {/if}
                                <div class="min-w-0">
                                    <div
                                        class="truncate font-medium text-primary hover:underline"
                                    >
                                        {row.team_name}
                                    </div>
                                    {#if row.description}
                                        <div class="truncate text-xs text-muted-foreground">
                                            {row.description}
                                        </div>
                                    {/if}
                                </div>
                            </Link>
                        </td>
                        <td class="px-3 py-3 text-center tabular-nums">{row.played}</td>
                        <td class="px-3 py-3 text-center tabular-nums">{row.win}</td>
                        <td class="px-3 py-3 text-center tabular-nums">{row.draw}</td>
                        <td class="px-3 py-3 text-center tabular-nums">{row.lose}</td>
                        <td class="px-3 py-3 text-center tabular-nums">
                            {row.goals_for}:{row.goals_against}
                        </td>
                        <td class="px-3 py-3 text-center tabular-nums">
                            {formatGoalsDiff(row.goals_diff)}
                        </td>
                        <td class="px-3 py-3 text-center font-semibold tabular-nums"
                            >{row.points}</td
                        >
                        <td class="px-4 py-3">
                            {#if row.form}
                                <div class="flex items-center gap-1">
                                    {#each row.form.split('') as result, index (`${row.id}-${index}-${result}`)}
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
                {:else}
                    <tr>
                        <td
                            colspan="10"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Brak danych tabeli. Uruchom synchronizację:
                            <code class="mx-1 rounded bg-muted px-1.5 py-0.5 text-xs"
                                >php artisan football:sync-standings</code
                            >
                        </td>
                    </tr>
                {/each}
            </tbody>
        </table>
    </div>
</div>
