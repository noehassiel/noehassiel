<x-layouts.auth :title="__('Confirm password')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirm password')"
            :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify
            options-route="passkey.confirm-options"
            submit-route="passkey.confirm"
            :label="__('Confirm with passkey')"
            :loading-label="__('Confirming...')"
            :separator="__('Or confirm with password')"
        />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Password -->
            <x-ui.field>
                <x-ui.label>{{ __('Password') }}</x-ui.label>
                <x-ui.input
                    name="password"
                    type="password"
                    required
                    autofocus
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    :revealable="true"
                />
                <x-ui.error name="password" />
            </x-ui.field>

            <x-ui.button variant="primary" type="submit" class="w-full">{{ __('Confirm') }}</x-ui.button>
        </form>
    </div>
</x-layouts.auth>
