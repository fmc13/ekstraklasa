<script module lang="ts">
    import { index } from '@/routes/typowanie';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Typowanie',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import PredictionMatchList from '@/components/PredictionMatchList.svelte';
    import type { PredictionFixture } from '@/components/PredictionMatchList.svelte';

    type PredictionRound = {
        name: string;
        number: number | null;
        fixtures: PredictionFixture[];
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
        rounds?: PredictionRound[];
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

    function roundLabel(round: PredictionRound): string {
        if (round.number !== null) {
            return `Kolejka ${round.number}`;
        }

        return round.name;
    }
</script>

<AppHead title="Typowanie" />

<div class="flex w-full flex-col space-y-6 p-4">
    <Heading
        variant="small"
        title="Typowanie"
        description="Typuj wynik 1 / X / 2. Edycja możliwa do kickoffu — 1 punkt za trafienie. Sezon {league.season}/{league.season + 1}."
    />

    {#if roundOptions.length > 0}
        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                class="rounded-md border px-3 py-1.5 text-sm {activeRound ===
                'all'
                    ? 'border-primary bg-primary/10 font-medium'
                    : 'hover:bg-muted'}"
                onclick={() => (selectedRound = 'all')}
            >
                Wszystkie
            </button>
            {#each roundOptions as roundNumber (roundNumber)}
                <button
                    type="button"
                    class="rounded-md border px-3 py-1.5 text-sm {activeRound ===
                    roundNumber
                        ? 'border-primary bg-primary/10 font-medium'
                        : 'hover:bg-muted'}"
                    onclick={() => (selectedRound = roundNumber)}
                >
                    {roundNumber}
                </button>
            {/each}
        </div>
    {/if}

    {#if visibleRounds.length === 0}
        <p class="text-sm text-muted-foreground">
            Brak meczów do typowania. Zsynchronizuj terminarz.
        </p>
    {:else}
        <div class="space-y-8">
            {#each visibleRounds as round (round.name)}
                <section class="space-y-4">
                    {#if activeRound === 'all'}
                        <h2 class="text-lg font-semibold">{roundLabel(round)}</h2>
                    {/if}
                    <PredictionMatchList fixtures={round.fixtures} />
                </section>
            {/each}
        </div>
    {/if}
</div>
