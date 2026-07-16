<script module lang="ts">
    import { index } from '@/routes/users';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Użytkownicy',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link, page } from '@inertiajs/svelte';
    import UserController from '@/actions/App/Http/Controllers/UserController';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { Button } from '@/components/ui/button';
    import { create, edit } from '@/routes/users';

    type ManagedUser = {
        id: number;
        name: string;
        surname: string;
        full_name: string;
        email: string;
        role: string | null;
    };

    let {
        users,
    }: {
        users: ManagedUser[];
    } = $props();

    const authUserId = $derived(page.props.auth.user?.id);
</script>

<AppHead title="Użytkownicy" />

<div class="flex flex-col gap-6 p-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <Heading
            title="Użytkownicy"
            description="Dodawaj, edytuj i usuwaj konta użytkowników"
        />
        <Button asChild class="w-fit">
            {#snippet children(props)}
                <Link {...props} href={create()}>Dodaj użytkownika</Link>
            {/snippet}
        </Button>
    </div>

    <div
        class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
    >
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="border-b bg-muted/50 text-muted-foreground">
                <tr>
                    <th class="px-4 py-3 font-medium">Imię</th>
                    <th class="px-4 py-3 font-medium">Nazwisko</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Rola</th>
                    <th class="px-4 py-3 text-right font-medium">Akcje</th>
                </tr>
            </thead>
            <tbody>
                {#each users as user (user.id)}
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-3">{user.name}</td>
                        <td class="px-4 py-3">{user.surname}</td>
                        <td class="px-4 py-3">{user.email}</td>
                        <td class="px-4 py-3 capitalize">{user.role ?? '—'}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <Button variant="outline" size="sm" asChild>
                                    {#snippet children(props)}
                                        <Link {...props} href={edit(user.id)}
                                            >Edytuj</Link
                                        >
                                    {/snippet}
                                </Button>
                                {#if user.id !== authUserId}
                                    <Form
                                        {...UserController.destroy.form(user.id)}
                                        options={{
                                            onBefore: () =>
                                                confirm(
                                                    `Usunąć użytkownika ${user.full_name}?`,
                                                ),
                                        }}
                                    >
                                        {#snippet children({ processing })}
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                size="sm"
                                                disabled={processing}
                                            >
                                                Usuń
                                            </Button>
                                        {/snippet}
                                    </Form>
                                {/if}
                            </div>
                        </td>
                    </tr>
                {:else}
                    <tr>
                        <td
                            colspan="5"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Brak użytkowników.
                        </td>
                    </tr>
                {/each}
            </tbody>
        </table>
    </div>
</div>
