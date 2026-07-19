<script module lang="ts">
    import { index } from '@/routes/calendar';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Kalendarz',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ChevronLeft from 'lucide-svelte/icons/chevron-left';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Button } from '@/components/ui/button';
    import { toUrl } from '@/lib/utils';
    import { index as calendarIndex } from '@/routes/calendar';
    import { show as teamShow } from '@/routes/teams';

    type CalendarFixture = {
        id: number;
        date: string | null;
        kickoff_at: string | null;
        status_short: string | null;
        home_team_id: number;
        home_team_name: string;
        home_team_logo: string | null;
        away_team_id: number;
        away_team_name: string;
        away_team_logo: string | null;
        home_goals: number | null;
        away_goals: number | null;
    };

    type MonthRef = {
        year: number;
        month: number;
    };

    type LeagueInfo = {
        id: number;
        name: string;
        season: number;
    };

    type CalendarDay = {
        key: string;
        date: Date;
        day: number;
        inMonth: boolean;
        isToday: boolean;
        fixtures: CalendarFixture[];
    };

    let {
        year,
        month,
        previous,
        next,
        fixtures = [],
        league,
    }: {
        year: number;
        month: number;
        previous: MonthRef;
        next: MonthRef;
        fixtures?: CalendarFixture[];
        league: LeagueInfo;
    } = $props();

    const weekdayLabels = ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'So', 'Nd'];

    const monthLabel = $derived(
        new Date(year, month - 1, 1).toLocaleDateString('pl-PL', {
            month: 'long',
            year: 'numeric',
        }),
    );

    const fixturesByDate = $derived.by(() => {
        const map = new Map<string, CalendarFixture[]>();

        for (const fixture of fixtures) {
            if (!fixture.date) {
                continue;
            }

            const dayFixtures = map.get(fixture.date) ?? [];
            dayFixtures.push(fixture);
            map.set(fixture.date, dayFixtures);
        }

        return map;
    });

    const weeks = $derived.by((): CalendarDay[][] => {
        const firstOfMonth = new Date(year, month - 1, 1);
        const startOffset = (firstOfMonth.getDay() + 6) % 7;
        const gridStart = new Date(year, month - 1, 1 - startOffset);
        const todayKey = dateKey(new Date());
        const result: CalendarDay[][] = [];

        for (let week = 0; week < 6; week++) {
            const days: CalendarDay[] = [];

            for (let weekday = 0; weekday < 7; weekday++) {
                const date = new Date(
                    gridStart.getFullYear(),
                    gridStart.getMonth(),
                    gridStart.getDate() + week * 7 + weekday,
                );
                const key = dateKey(date);

                days.push({
                    key,
                    date,
                    day: date.getDate(),
                    inMonth: date.getMonth() === month - 1,
                    isToday: key === todayKey,
                    fixtures: fixturesByDate.get(key) ?? [],
                });
            }

            result.push(days);
        }

        const lastWeek = result[result.length - 1];
        if (lastWeek.every((day) => !day.inMonth)) {
            result.pop();
        }

        return result;
    });

    function dateKey(date: Date): string {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');

        return `${y}-${m}-${d}`;
    }

    function monthUrl(ref: MonthRef): string {
        return toUrl(
            calendarIndex({
                query: {
                    year: ref.year,
                    month: ref.month,
                },
            }),
        );
    }

    function formatKickoffTime(value: string | null): string {
        if (!value) {
            return '';
        }

        return new Date(value).toLocaleTimeString('pl-PL', {
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function scoreOrVs(fixture: CalendarFixture): string {
        if (fixture.home_goals === null || fixture.away_goals === null) {
            return formatKickoffTime(fixture.kickoff_at) || 'vs';
        }

        return `${fixture.home_goals}:${fixture.away_goals}`;
    }

    function shortTeamName(name: string): string {
        const parts = name.trim().split(/\s+/);

        if (parts.length === 1) {
            return parts[0].slice(0, 8);
        }

        return parts
            .map((part) => part[0])
            .join('')
            .slice(0, 4)
            .toUpperCase();
    }
</script>

<AppHead title="Kalendarz" />

<div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
    <Heading
        title="Kalendarz"
        description="Terminarz {league.name} w sezonie {league.season}/{league.season + 1}."
    />

    <div class="flex items-center justify-between gap-3">
        <Button variant="outline" size="icon" asChild>
            {#snippet children(props)}
                <Link
                    {...props}
                    href={monthUrl(previous)}
                    prefetch
                    aria-label="Poprzedni miesiąc"
                >
                    <ChevronLeft class="size-4" />
                </Link>
            {/snippet}
        </Button>

        <h2 class="text-lg font-semibold tracking-tight capitalize">{monthLabel}</h2>

        <Button variant="outline" size="icon" asChild>
            {#snippet children(props)}
                <Link
                    {...props}
                    href={monthUrl(next)}
                    prefetch
                    aria-label="Następny miesiąc"
                >
                    <ChevronRight class="size-4" />
                </Link>
            {/snippet}
        </Button>
    </div>

    <div
        class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
    >
        <div
            class="grid grid-cols-7 border-b border-sidebar-border/70 bg-muted/40 text-center text-xs font-medium text-muted-foreground dark:border-sidebar-border"
        >
            {#each weekdayLabels as label (label)}
                <div class="px-1 py-2 sm:px-2">{label}</div>
            {/each}
        </div>

        <div class="grid grid-cols-7">
            {#each weeks as week (week[0].key)}
                {#each week as day (day.key)}
                    <div
                        class="min-h-24 border-b border-r border-sidebar-border/50 p-1.5 last:border-r-0 sm:min-h-28 sm:p-2 dark:border-sidebar-border/60 {day.inMonth
                            ? 'bg-background'
                            : 'bg-muted/20 text-muted-foreground'} {day.isToday
                            ? 'ring-1 ring-inset ring-primary/40'
                            : ''}"
                    >
                        <div
                            class="mb-1 flex size-6 items-center justify-center rounded-full text-xs font-medium {day.isToday
                                ? 'bg-primary text-primary-foreground'
                                : ''}"
                        >
                            {day.day}
                        </div>

                        <div class="space-y-1">
                            {#each day.fixtures as fixture (fixture.id)}
                                <div
                                    class="rounded-md border border-border/60 bg-card px-1 py-1 text-[10px] leading-tight sm:px-1.5 sm:text-xs"
                                    title="{fixture.home_team_name} vs {fixture.away_team_name}"
                                >
                                    <div
                                        class="flex items-center justify-between gap-1 text-muted-foreground"
                                    >
                                        <span class="tabular-nums"
                                            >{scoreOrVs(fixture)}</span
                                        >
                                    </div>
                                    <div class="mt-0.5 flex items-center gap-1">
                                        {#if fixture.home_team_logo}
                                            <img
                                                src={fixture.home_team_logo}
                                                alt=""
                                                class="size-3.5 object-contain sm:size-4"
                                                loading="lazy"
                                            />
                                        {/if}
                                        <Link
                                            href={toUrl(teamShow(fixture.home_team_id))}
                                            class="truncate hover:text-primary"
                                            prefetch
                                        >
                                            <span class="hidden sm:inline"
                                                >{fixture.home_team_name}</span
                                            >
                                            <span class="sm:hidden"
                                                >{shortTeamName(fixture.home_team_name)}</span
                                            >
                                        </Link>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        {#if fixture.away_team_logo}
                                            <img
                                                src={fixture.away_team_logo}
                                                alt=""
                                                class="size-3.5 object-contain sm:size-4"
                                                loading="lazy"
                                            />
                                        {/if}
                                        <Link
                                            href={toUrl(teamShow(fixture.away_team_id))}
                                            class="truncate hover:text-primary"
                                            prefetch
                                        >
                                            <span class="hidden sm:inline"
                                                >{fixture.away_team_name}</span
                                            >
                                            <span class="sm:hidden"
                                                >{shortTeamName(fixture.away_team_name)}</span
                                            >
                                        </Link>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>
                {/each}
            {/each}
        </div>
    </div>

    {#if fixtures.length === 0}
        <p class="text-center text-sm text-muted-foreground">
            Brak meczów w tym miesiącu.
        </p>
    {/if}
</div>
