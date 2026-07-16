<script module lang="ts">
    import { create, index } from '@/routes/users';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Użytkownicy',
                href: index(),
            },
            {
                title: 'Dodaj',
                href: create(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import UserController from '@/actions/App/Http/Controllers/UserController';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { index as usersIndex } from '@/routes/users';

    let {
        roles,
    }: {
        roles: Array<{ value: string; label: string }>;
    } = $props();
</script>

<AppHead title="Dodaj użytkownika" />

<div class="flex flex-col gap-6 p-4">
    <Heading
        title="Dodaj użytkownika"
        description="Utwórz nowe konto i przypisz rolę"
    />

    <Form {...UserController.store.form()} class="max-w-xl space-y-6">
        {#snippet children({ errors, processing })}
            <div class="grid gap-2">
                <Label for="name">Imię</Label>
                <Input id="name" name="name" required autocomplete="given-name" />
                <InputError message={errors.name} />
            </div>

            <div class="grid gap-2">
                <Label for="surname">Nazwisko</Label>
                <Input
                    id="surname"
                    name="surname"
                    required
                    autocomplete="family-name"
                />
                <InputError message={errors.surname} />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autocomplete="username"
                />
                <InputError message={errors.email} />
            </div>

            <div class="grid gap-2">
                <Label for="role">Rola</Label>
                <select
                    id="role"
                    name="role"
                    required
                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                >
                    {#each roles as role (role.value)}
                        <option value={role.value}>{role.label}</option>
                    {/each}
                </select>
                <InputError message={errors.role} />
            </div>

            <div class="grid gap-2">
                <Label for="password">Hasło</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                />
                <InputError message={errors.password} />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Potwierdź hasło</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" disabled={processing}>Zapisz</Button>
                <Button variant="outline" asChild>
                    {#snippet children(props)}
                        <Link {...props} href={usersIndex()}>Anuluj</Link>
                    {/snippet}
                </Button>
            </div>
        {/snippet}
    </Form>
</div>
