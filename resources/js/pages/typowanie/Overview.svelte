<script module lang="ts">
    import { overview } from '@/routes/typowanie';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Przegląd typów',
                href: overview(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';

    type OverviewFixture = {
        id: number;
        home_team_name: string;
        home_team_logo: string | null;
        away_team_name: string;
        away_team_logo: string | null;
        kickoff_at_label: string;
        score_label: string;
        is_played: boolean;
        actual_result: string | null;
    };

    type OverviewPlayer = {
        id: number;
        name: string;
        surname: string;
        points: number;
        predictions: Array<string | null>;
    };

    let {
        round_number = null,
        fixtures = [],
        players = [],
    }: {
        round_number?: number | null;
        fixtures?: OverviewFixture[];
        players?: OverviewPlayer[];
    } = $props();

    const description = $derived(
        round_number !== null
            ? `Typowania wszystkich graczy na kolejkę ${round_number}`
            : 'Typowania wszystkich graczy na najbliższą kolejkę',
    );
</script>

<AppHead title="Przegląd typów" />

<div class="flex w-full flex-col space-y-6 p-4">
    <Heading variant="small" title="Przegląd typów" {description} />

    {#if fixtures.length === 0}
        <p class="text-sm text-muted-foreground">
            Brak meczów w najbliższej kolejce.
        </p>
    {:else if players.length === 0}
        <p class="text-sm text-muted-foreground">Brak graczy w systemie.</p>
    {:else}
        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full min-w-max text-sm">
                <thead class="border-b bg-muted/50">
                    <tr>
                        <th
                            class="sticky left-0 z-10 min-w-40 border-r bg-muted/50 px-4 py-3 text-left font-medium whitespace-nowrap"
                        >
                            Gracz
                        </th>
                        <th
                            class="sticky left-40 z-10 min-w-16 border-r bg-muted/50 px-3 py-3 text-center font-medium whitespace-nowrap"
                        >
                            Pkt
                        </th>
                        {#each fixtures as fixture (fixture.id)}
                            <th class="min-w-36 px-3 py-3 text-center font-medium">
                                <div class="space-y-1">
                                    <p class="text-xs text-muted-foreground">
                                        {fixture.kickoff_at_label}
                                    </p>
                                    <div
                                        class="flex items-center justify-center gap-1.5"
                                    >
                                        {#if fixture.home_team_logo}
                                            <img
                                                src={fixture.home_team_logo}
                                                alt=""
                                                class="size-4 object-contain"
                                                loading="lazy"
                                            />
                                        {/if}
                                        <span class="text-xs font-semibold">
                                            {fixture.home_team_name}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-muted-foreground">
                                        {fixture.score_label}
                                    </p>
                                    <div
                                        class="flex items-center justify-center gap-1.5"
                                    >
                                        {#if fixture.away_team_logo}
                                            <img
                                                src={fixture.away_team_logo}
                                                alt=""
                                                class="size-4 object-contain"
                                                loading="lazy"
                                            />
                                        {/if}
                                        <span class="text-xs font-semibold">
                                            {fixture.away_team_name}
                                        </span>
                                    </div>
                                    {#if fixture.actual_result}
                                        <p class="text-[10px] font-medium text-muted-foreground">
                                            Wynik: {fixture.actual_result}
                                        </p>
                                    {/if}
                                </div>
                            </th>
                        {/each}
                    </tr>
                </thead>
                <tbody>
                    {#each players as player (player.id)}
                        <tr class="border-b last:border-b-0">
                            <td
                                class="sticky left-0 z-10 border-r bg-background px-4 py-3 font-medium whitespace-nowrap"
                            >
                                {player.name}
                                {player.surname}
                            </td>
                            <td
                                class="sticky left-40 z-10 border-r bg-background px-3 py-3 text-center font-semibold tabular-nums"
                            >
                                {player.points}
                            </td>
                            {#each player.predictions as prediction, index (fixtures[index].id)}
                                <td class="px-3 py-3 text-center">
                                    {#if prediction}
                                        <span
                                            class="inline-flex min-w-8 justify-center rounded-md border bg-muted/30 px-2 py-1 font-semibold tabular-nums"
                                        >
                                            {prediction}
                                        </span>
                                    {:else}
                                        <span class="text-muted-foreground">—</span>
                                    {/if}
                                </td>
                            {/each}
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    {/if}
</div>
