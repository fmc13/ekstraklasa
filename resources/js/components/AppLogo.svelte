<script lang="ts">
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';

    let {
        fullWidth = false,
    }: {
        fullWidth?: boolean;
    } = $props();
</script>

<div class={fullWidth ? 'flex w-full items-center' : 'flex items-center'}>
    <AppLogoIcon
        class={fullWidth ? 'h-auto w-full' : 'h-8 w-auto max-w-[200px]'}
    />
    <span class="sr-only">Ekstraklasa</span>
</div>
