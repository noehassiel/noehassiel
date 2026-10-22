<x-layouts.auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <x-ui.field>
                <x-ui.label>{{ __('Email address') }}</x-ui.label>
                <x-ui.input
                    name="email"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email webauthn"
                    placeholder="email@example.com"
                />
                <x-ui.error name="email" />
            </x-ui.field>

            <!-- Password -->
            <div class="relative">
                <x-ui.field>
                    <x-ui.label>{{ __('Password') }}</x-ui.label>
                    <x-ui.input
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        :placeholder="__('Password')"
                        :revealable="true"
                    />
                    <x-ui.error name="password" />
                </x-ui.field>

                <x-ui.link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </x-ui.link>
            </div>

            <!-- Remember Me -->
            <x-ui.checkbox name="remember" label="{{ __('Remember me') }}" :checked="(bool) old('remember')" />

            <x-ui.button variant="primary" type="submit" class="w-full">{{ __('Log in') }}</x-ui.button>
        </form>
    </div>
</x-layouts.auth>
