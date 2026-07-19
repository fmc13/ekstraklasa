<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Calendar from 'lucide-svelte/icons/calendar';
    import CalendarDays from 'lucide-svelte/icons/calendar-days';
    import Dices from 'lucide-svelte/icons/dices';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Table2 from 'lucide-svelte/icons/table-2';
    import Shield from 'lucide-svelte/icons/shield';
    import Trophy from 'lucide-svelte/icons/trophy';
    import UserRound from 'lucide-svelte/icons/user-round';
    import Users from 'lucide-svelte/icons/users';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import { index as calendarIndex } from '@/routes/calendar';
    import { index as matchesIndex } from '@/routes/matches';
    import { index as playersIndex } from '@/routes/players';
    import { index as rankingIndex } from '@/routes/ranking';
    import { index as teamsIndex } from '@/routes/teams';
    import { index as typowanieIndex, overview as typowanieOverview } from '@/routes/typowanie';
    import { index as usersIndex } from '@/routes/users';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const mainNavItems: NavItem[] = [
        {
            title: 'Ranking',
            href: rankingIndex(),
            icon: Trophy,
        },
        {
            title: 'Ekstraklasa',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Kluby',
            href: teamsIndex(),
            icon: Shield,
        },
        {
            title: 'Zawodnicy',
            href: playersIndex(),
            icon: UserRound,
        },
        {
            title: 'Mecze',
            href: matchesIndex(),
            icon: CalendarDays,
        },
        {
            title: 'Kalendarz',
            href: calendarIndex(),
            icon: Calendar,
        },
        {
            title: 'Typowanie',
            href: typowanieIndex(),
            icon: Dices,
        },
        {
            title: 'Przegląd typów',
            href: typowanieOverview(),
            icon: Table2,
        },
    ];

    const footerNavItems = $derived.by((): NavItem[] => {
        const items: NavItem[] = [];

        if (page.props.canManageUsers) {
            items.push({
                title: 'Użytkownicy',
                href: usersIndex(),
                icon: Users,
            });
        }

        return items;
    });
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader class="p-0">
        <SidebarMenu class="gap-0">
            <SidebarMenuItem>
                <SidebarMenuButton
                    size="lg"
                    asChild
                    class="h-auto! rounded-none p-0 hover:bg-transparent active:bg-transparent data-[active=true]:bg-transparent group-data-[collapsible=icon]:m-2 group-data-[collapsible=icon]:size-8! group-data-[collapsible=icon]:rounded-md group-data-[collapsible=icon]:p-0!"
                >
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={toUrl(dashboard())}
                            class={props.class}
                        >
                            <AppLogo fullWidth />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />
    </SidebarContent>

    <SidebarFooter>
        {#if footerNavItems.length > 0}
            <NavFooter items={footerNavItems} />
        {/if}
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
