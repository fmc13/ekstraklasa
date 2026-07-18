<script module lang="ts">
    import { index } from '@/routes/ranking';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Ranking',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';

    type LeaderboardEntry = {
        position: number;
        name: string;
        surname: string;
        predictions_count: number;
        hits: number;
        misses: number;
        accuracy_percent: number;
        points: number;
    };

    let {
        leaderboard = [],
    }: {
        leaderboard?: LeaderboardEntry[];
    } = $props();
</script>

<AppHead title="Ranking" />

<div class="flex w-full flex-col space-y-6 p-4">
    <Heading
        variant="small"
        title="Ranking typowania"
        description="1 punkt za trafiony wynik 1 / X / 2. Remisy punktowe dzielą pozycję."
    />

    {#if leaderboard.length === 0}
        <p class="text-sm text-muted-foreground">Brak graczy w rankingu.</p>
    {:else}
        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full min-w-[36rem] text-left text-sm">
                <thead class="border-b bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Gracz</th>
                        <th class="px-4 py-3 text-right font-medium">Punkty</th>
                        <th class="px-4 py-3 text-right font-medium">Trafione</th>
                        <th class="px-4 py-3 text-right font-medium">Pudła</th>
                        <th class="px-4 py-3 text-right font-medium">Skuteczność</th>
                        <th class="px-4 py-3 text-right font-medium">Typy</th>
                    </tr>
                </thead>
                <tbody>
                    {#each leaderboard as entry (entry.name + entry.surname + entry.position)}
                        <tr class="border-b last:border-0">
                            <td class="px-4 py-3 tabular-nums text-muted-foreground">
                                {entry.position}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {entry.name}
                                {entry.surname}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums">
                                {entry.points}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-muted-foreground">
                                {entry.hits}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-muted-foreground">
                                {entry.misses}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-muted-foreground">
                                {entry.accuracy_percent}%
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-muted-foreground">
                                {entry.predictions_count}
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    {/if}
</div>
