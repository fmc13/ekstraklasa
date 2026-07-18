<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import PredictionController from '@/actions/App/Http/Controllers/PredictionController';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Spinner } from '@/components/ui/spinner';

    export type PredictionFixture = {
        id: number;
        home_team_id: number;
        home_team_name: string;
        home_team_logo: string | null;
        away_team_id: number;
        away_team_name: string;
        away_team_logo: string | null;
        round: string;
        round_number: number | null;
        kickoff_at: string | null;
        kickoff_at_label: string;
        can_predict: boolean;
        prediction: string | null;
        actual_result: string | null;
        is_correct: boolean | null;
        awarded_points: number;
        is_played: boolean;
        score_label: string;
    };

    let {
        fixtures,
        emptyMessage = 'Brak meczów w tej kolejce.',
    }: {
        fixtures: PredictionFixture[];
        emptyMessage?: string;
    } = $props();

    const options = [
        { value: '1', label: '1' },
        { value: 'X', label: 'X' },
        { value: '2', label: '2' },
    ] as const;
</script>

{#if fixtures.length === 0}
    <p class="text-sm text-muted-foreground">{emptyMessage}</p>
{:else}
    <div class="space-y-4">
        {#each fixtures as fixture (fixture.id)}
            <div class="overflow-hidden rounded-lg border">
                <div class="border-b bg-muted/50 px-4 py-3">
                    <p class="text-sm font-semibold">{fixture.kickoff_at_label}</p>
                    {#if fixture.round_number !== null}
                        <p class="text-xs text-muted-foreground">
                            Kolejka {fixture.round_number}
                        </p>
                    {/if}
                </div>

                <div class="space-y-4 px-4 py-4">
                    <div class="flex items-center gap-2 sm:gap-4">
                        <div
                            class="flex min-w-0 flex-1 items-center justify-end gap-1.5 sm:gap-3"
                        >
                            <span
                                class="truncate text-right text-sm font-medium sm:text-base"
                            >
                                {fixture.home_team_name}
                            </span>
                            {#if fixture.home_team_logo}
                                <img
                                    src={fixture.home_team_logo}
                                    alt=""
                                    class="size-6 shrink-0 object-contain sm:size-7"
                                    loading="lazy"
                                />
                            {:else}
                                <span class="size-6 shrink-0 sm:size-7"></span>
                            {/if}
                        </div>

                        <div
                            class="shrink-0 px-1 text-center text-base font-semibold tabular-nums sm:text-lg"
                        >
                            {fixture.score_label}
                        </div>

                        <div
                            class="flex min-w-0 flex-1 items-center gap-1.5 sm:gap-3"
                        >
                            {#if fixture.away_team_logo}
                                <img
                                    src={fixture.away_team_logo}
                                    alt=""
                                    class="size-6 shrink-0 object-contain sm:size-7"
                                    loading="lazy"
                                />
                            {:else}
                                <span class="size-6 shrink-0 sm:size-7"></span>
                            {/if}
                            <span
                                class="truncate text-sm font-medium sm:text-base"
                            >
                                {fixture.away_team_name}
                            </span>
                        </div>
                    </div>

                    {#if fixture.can_predict}
                        <Form
                            {...PredictionController.store.form(fixture.id)}
                            options={{ preserveScroll: true }}
                        >
                            {#snippet children({ errors, processing })}
                                <div class="flex flex-wrap items-center gap-3">
                                    {#each options as option (option.value)}
                                        <label
                                            class="flex cursor-pointer items-center gap-2 rounded-md border px-4 py-2 text-sm font-medium has-checked:border-primary has-checked:bg-primary/10"
                                        >
                                            <input
                                                type="radio"
                                                name="result"
                                                value={option.value}
                                                checked={fixture.prediction ===
                                                    option.value}
                                                required
                                                class="sr-only"
                                            />
                                            {option.label}
                                        </label>
                                    {/each}

                                    <Button
                                        type="submit"
                                        size="sm"
                                        disabled={processing}
                                        data-test="save-prediction-{fixture.id}"
                                    >
                                        {#if processing}<Spinner />{/if}
                                        Zapisz typ
                                    </Button>
                                </div>
                                <InputError message={errors.result} />
                            {/snippet}
                        </Form>
                    {:else if fixture.prediction}
                        <div class="flex flex-wrap items-center gap-3 text-sm">
                            <span class="text-muted-foreground">Twój typ:</span>
                            <span class="font-semibold">{fixture.prediction}</span>

                            {#if fixture.is_played}
                                <span class="text-muted-foreground">·</span>
                                <span class="text-muted-foreground">Wynik:</span>
                                <span class="font-semibold">
                                    {fixture.actual_result}
                                </span>

                                {#if fixture.is_correct}
                                    <span
                                        class="rounded-full bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-400"
                                    >
                                        Trafione (+{fixture.awarded_points} pkt)
                                    </span>
                                {:else}
                                    <span
                                        class="rounded-full bg-red-500/10 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-400"
                                    >
                                        Nietrafione
                                    </span>
                                {/if}
                            {/if}
                        </div>
                    {:else}
                        <p class="text-sm text-muted-foreground">
                            Typowanie zamknięte — nie oddano typu.
                        </p>
                    {/if}
                </div>
            </div>
        {/each}
    </div>
{/if}
