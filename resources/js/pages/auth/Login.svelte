<script lang="ts">
    import { Form, Link } from '@inertiajs/svelte';
    import Lock from 'lucide-svelte/icons/lock';
    import Mail from 'lucide-svelte/icons/mail';
    import AppHead from '@/components/AppHead.svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Spinner } from '@/components/ui/spinner';
    import { home } from '@/routes';
    import { store } from '@/routes/login';
    import { request } from '@/routes/password';

    let {
        status = '',
        canResetPassword,
    }: {
        status?: string;
        canResetPassword: boolean;
    } = $props();

    const fieldClass =
        'h-12 w-full rounded-full border border-white/35 bg-white/10 px-5 pr-12 text-sm text-white shadow-none outline-none transition placeholder:text-white/70 focus:border-white/70 focus:bg-white/15 focus:ring-2 focus:ring-white/30';
</script>

<AppHead title="Logowanie" />

<div
    class="rounded-2xl border border-white/25 bg-white/10 px-6 py-8 shadow-2xl backdrop-blur-xl sm:px-8 sm:py-10"
>
    <div class="mb-8 flex flex-col items-center gap-4 text-center">
        <Link href={home()} class="flex items-center justify-center">
            <AppLogoIcon class="h-12 w-auto max-w-[240px]" />
            <span class="sr-only">Ekstraklasa</span>
        </Link>
        <h1 class="text-3xl font-semibold tracking-tight text-white">
            Logowanie
        </h1>
    </div>

    {#if status}
        <div
            class="mb-4 rounded-full border border-emerald-300/40 bg-emerald-500/20 px-4 py-2 text-center text-sm font-medium text-emerald-100"
        >
            {status}
        </div>
    {/if}

    <Form
        {...store.form()}
        resetOnSuccess={['password']}
        class="flex flex-col gap-5"
    >
        {#snippet children({ errors, processing })}
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <div class="relative">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="Adres e-mail"
                            class={fieldClass}
                        />
                        <Mail
                            class="pointer-events-none absolute top-1/2 right-4 size-4 -translate-y-1/2 text-white/80"
                        />
                    </div>
                    <InputError message={errors.email} />
                </div>

                <div class="grid gap-2">
                    <div class="relative">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Hasło"
                            class={fieldClass}
                        />
                        <Lock
                            class="pointer-events-none absolute top-1/2 right-4 size-4 -translate-y-1/2 text-white/80"
                        />
                    </div>
                    <InputError message={errors.password} />
                </div>

                <div class="flex items-center justify-between gap-3 px-1 text-sm text-white">
                    <label
                        for="remember"
                        class="flex cursor-pointer items-center gap-2"
                    >
                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            class="size-4 rounded border-white/50 bg-transparent text-primary accent-white"
                        />
                        <span>Zapamiętaj mnie</span>
                    </label>

                    {#if canResetPassword}
                        <Link
                            href={request()}
                            class="text-white/90 underline-offset-4 hover:text-white hover:underline"
                        >
                            Zapomniałeś hasła?
                        </Link>
                    {/if}
                </div>

                <button
                    type="submit"
                    class="mt-2 inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-white text-sm font-semibold text-emerald-950 transition hover:bg-white/90 disabled:opacity-60"
                    disabled={processing}
                    data-test="login-button"
                >
                    {#if processing}<Spinner />{/if}
                    Zaloguj się
                </button>
            </div>
        {/snippet}
    </Form>
</div>
