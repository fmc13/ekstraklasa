<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import FolderGit2 from 'lucide-svelte/icons/folder-git-2';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Shield from 'lucide-svelte/icons/shield';
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
    import { index as teamsIndex } from '@/routes/teams';
    import { index as usersIndex } from '@/routes/users';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const mainNavItems: NavItem[] = [
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

        items.push(
            {
                title: 'Repository',
                href: 'https://github.com/laravel/svelte-starter-kit',
                icon: FolderGit2,
                external: true,
            },
            {
                title: 'Documentation',
                href: 'https://laravel.com/docs/starter-kits#svelte',
                icon: BookOpen,
                external: true,
            },
        );

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
        <NavFooter items={footerNavItems} />
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
