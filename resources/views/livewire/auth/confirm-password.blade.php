<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Confirm password')"
        :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
    />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="confirmPassword" class="flex flex-col gap-6">
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

        <x-ui.button variant="primary" type="submit" class="w-full">{{ __('Confirm') }}</x-ui.button>
    </form>
</div>
