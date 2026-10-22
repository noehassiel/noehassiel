<x-layouts.auth :title="__('Reset password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <x-ui.field>
                <x-ui.label>{{ __('Email') }}</x-ui.label>
                <x-ui.input
                    name="email"
                    :value="old('email', request('email'))"
                    type="email"
                    required
                    autocomplete="email"
                />
                <x-ui.error name="email" />
            </x-ui.field>

            <!-- Password -->
            <x-ui.field>
                <x-ui.label>{{ __('Password') }}</x-ui.label>
                <x-ui.input
                    name="password"
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
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Confirm password')"
                    :revealable="true"
                />
                <x-ui.error name="password_confirmation" />
            </x-ui.field>

            <x-ui.button type="submit" variant="primary" class="w-full">{{ __('Reset password') }}</x-ui.button>
        </form>
    </div>
</x-layouts.auth>
