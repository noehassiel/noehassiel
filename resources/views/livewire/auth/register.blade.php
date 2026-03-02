<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <x-ui.field>
            <x-ui.label>{{ __('Name') }}</x-ui.label>
            <x-ui.input
                wire:model="name"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />
            <x-ui.error name="name" />
        </x-ui.field>

        <!-- Email Address -->
        <x-ui.field>
            <x-ui.label>{{ __('Email address') }}</x-ui.label>
            <x-ui.input
                wire:model="email"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />
            <x-ui.error name="email" />
        </x-ui.field>

        <!-- Password -->
        <x-ui.field>
            <x-ui.label>{{ __('Password') }}</x-ui.label>
            <x-ui.input
                wire:model="password"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                :revealable="true"
            />
            <x-ui.error name="password" />
        </x-ui.field>

        <!-- Confirm Password -->
        <x-ui.field>
            <x-ui.label>{{ __('Confirm password') }}</x-ui.label>
            <x-ui.input
                wire:model="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                :revealable="true"
            />
            <x-ui.error name="password_confirmation" />
        </x-ui.field>

        <div class="flex items-center justify-end">
            <x-ui.button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </x-ui.button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <x-ui.link :href="route('login')" wire:navigate>{{ __('Log in') }}</x-ui.link>
    </div>
</div>
