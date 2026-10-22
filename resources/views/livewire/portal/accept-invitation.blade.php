<div class="flex flex-col gap-6">
    @if ($invitation)
        <x-auth-header
            :title="__('portal.invitation.title')"
            :description="__('portal.invitation.description', ['client' => $invitation->client->name])"
        />

        @error('invitation')
            <x-ui.alerts variant="error">{{ $message }}</x-ui.alerts>
        @enderror

        <form wire:submit="accept" class="flex flex-col gap-6">
            <x-ui.field>
                <x-ui.label>{{ __('Email') }}</x-ui.label>
                <x-ui.input :value="$invitation->email" type="email" readonly disabled />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('portal.invitation.name') }}</x-ui.label>
                <x-ui.input wire:model="name" required autofocus autocomplete="name" />
                <x-ui.error name="name" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('portal.invitation.password') }}</x-ui.label>
                <x-ui.input wire:model="password" type="password" required autocomplete="new-password" :revealable="true" />
                <x-ui.error name="password" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('portal.invitation.password_confirmation') }}</x-ui.label>
                <x-ui.input wire:model="password_confirmation" type="password" required autocomplete="new-password" :revealable="true" />
            </x-ui.field>

            <x-ui.button variant="primary" type="submit" class="w-full">{{ __('portal.invitation.submit') }}</x-ui.button>
        </form>
    @else
        <x-auth-header :title="__('portal.invitation.title')" :description="__('portal.invitation.invalid')" />

        <x-ui.button variant="outline" :href="route('login')" class="w-full">{{ __('Log in') }}</x-ui.button>
    @endif
</div>
