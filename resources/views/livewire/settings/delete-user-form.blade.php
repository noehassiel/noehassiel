<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <x-ui.heading>{{ __('Delete account') }}</x-ui.heading>
        <x-ui.description>{{ __('Delete your account and all of its resources') }}</x-ui.description>
    </div>

    <x-ui.modal
        id="confirm-user-deletion"
        heading="{{ __('Are you sure you want to delete your account?') }}"
        width="lg"
    >
        <x-slot:trigger>
            <x-ui.button variant="danger">
                {{ __('Delete account') }}
            </x-ui.button>
        </x-slot:trigger>

        <form wire:submit="deleteUser" class="space-y-6">
            <x-ui.description>
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </x-ui.description>

            <x-ui.field>
                <x-ui.label>{{ __('Password') }}</x-ui.label>
                <x-ui.input wire:model="password" type="password" :revealable="true" />
                <x-ui.error name="password" />
            </x-ui.field>

            <div class="flex justify-end gap-2">
                <x-ui.button x-on:click="$data.forseClose()" type="button" variant="solid">
                    {{ __('Cancel') }}
                </x-ui.button>
                <x-ui.button variant="danger" type="submit">{{ __('Delete account') }}</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</section>
