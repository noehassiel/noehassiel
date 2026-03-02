<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <x-ui.field>
            <x-ui.label>{{ __('Email address') }}</x-ui.label>
            <x-ui.input
                wire:model="email"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />
            <x-ui.error name="email" />
        </x-ui.field>

        <!-- Password -->
        <div class="relative">
            <x-ui.field>
                <x-ui.label>{{ __('Password') }}</x-ui.label>
                <x-ui.input
                    wire:model="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    :revealable="true"
                />
                <x-ui.error name="password" />
            </x-ui.field>

            @if (Route::has('password.request'))
                <x-ui.link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </x-ui.link>
            @endif
        </div>

        <!-- Remember Me -->
        <x-ui.checkbox wire:model="remember" label="{{ __('Remember me') }}" />

        <div class="flex items-center justify-end">
            <x-ui.button variant="primary" type="submit" class="w-full">{{ __('Log in') }}</x-ui.button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <x-ui.link :href="route('register')" wire:navigate>{{ __('Sign up') }}</x-ui.link>
        </div>
    @endif
</div>
