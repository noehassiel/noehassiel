<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <x-ui.field>
            <x-ui.label>{{ __('Email Address') }}</x-ui.label>
            <x-ui.input
                wire:model="email"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />
            <x-ui.error name="email" />
        </x-ui.field>

        <x-ui.button variant="primary" type="submit" class="w-full">{{ __('Email password reset link') }}</x-ui.button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        {{ __('Or, return to') }}
        <x-ui.link :href="route('login')" wire:navigate>{{ __('log in') }}</x-ui.link>
    </div>
</div>
