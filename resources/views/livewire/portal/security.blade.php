<div class="max-w-xl space-y-10">
    <div>
        <h1 class="text-4xl font-black tracking-tight sm:text-5xl">{{ __('portal.security.title') }}</h1>
        <p class="mt-3 text-[var(--silver)]">{{ __('Ensure your account is using a long, random password to stay secure') }}</p>
    </div>

    <div>
        @include('livewire.settings.partials.security-sections')
    </div>

    @include('livewire.settings.partials.security-modals')
</div>
