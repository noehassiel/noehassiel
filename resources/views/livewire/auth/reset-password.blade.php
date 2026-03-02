<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <!-- Email Address -->
        <x-ui.field>
            <x-ui.label>{{ __('Email') }}</x-ui.label>
            <x-ui.input
                wire:model="email"
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
                {{ __('Reset password') }}
            </x-ui.button>
        </div>
    </form>
</div>
