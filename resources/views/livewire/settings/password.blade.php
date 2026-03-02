<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            <x-ui.field>
                <x-ui.label>{{ __('Current password') }}</x-ui.label>
                <x-ui.input
                    wire:model="current_password"
                    type="password"
                    required
                    autocomplete="current-password"
                    :revealable="true"
                />
                <x-ui.error name="current_password" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('New password') }}</x-ui.label>
                <x-ui.input
                    wire:model="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    :revealable="true"
                />
                <x-ui.error name="password" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('Confirm Password') }}</x-ui.label>
                <x-ui.input
                    wire:model="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    :revealable="true"
                />
                <x-ui.error name="password_confirmation" />
            </x-ui.field>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <x-ui.button variant="primary" type="submit" class="w-full">{{ __('Save') }}</x-ui.button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
