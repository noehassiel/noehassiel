<x-ui.modal id="two-factor-setup-modal" width="md" :close-by-clicking-away="false" x-on:modal-closed="$wire.closeTwoFactorSetup()">
    <div class="space-y-6">
        <div class="space-y-2 text-center">
            <x-ui.heading>
                {{ $showVerificationStep ? __('Verify authentication code') : __('Enable two-factor authentication') }}
            </x-ui.heading>
            <x-ui.description>
                {{ $showVerificationStep
                    ? __('Enter the 6-digit code from your authenticator app.')
                    : __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.') }}
            </x-ui.description>
        </div>

        @if ($showVerificationStep)
            <div class="flex flex-col items-center gap-2">
                <x-ui.otp wire:model="code" length="6" autofocus />
                <x-ui.error name="code" />
            </div>

            <div class="flex gap-3">
                <x-ui.button variant="outline" class="flex-1" wire:click="resetVerification">{{ __('Back') }}</x-ui.button>
                <x-ui.button variant="primary" class="flex-1" wire:click="confirmTwoFactor">{{ __('Confirm') }}</x-ui.button>
            </div>
        @else
            @error('setupData')
                <x-ui.alerts variant="error" icon="x-circle">{{ $message }}</x-ui.alerts>
            @enderror

            <div class="flex justify-center">
                <div class="flex aspect-square w-64 items-center justify-center rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700">
                    @if ($qrCodeSvg)
                        {!! $qrCodeSvg !!}
                    @else
                        <x-ui.icon.loading />
                    @endif
                </div>
            </div>

            <x-ui.button variant="primary" class="w-full" wire:click="showVerification" :disabled="$errors->has('setupData')">
                {{ __('Continue') }}
            </x-ui.button>

            @if ($manualSetupKey)
                <div class="space-y-2">
                    <x-ui.text class="!text-center text-zinc-500">{{ __('or, enter the code manually') }}</x-ui.text>
                    <x-ui.input :value="$manualSetupKey" readonly :copyable="true" class="font-mono" />
                </div>
            @endif
        @endif
    </div>
</x-ui.modal>

<x-ui.modal id="delete-passkey-modal" width="md" :heading="__('Remove passkey')">
    <div class="space-y-6">
        <x-ui.text>
            {{ __('Are you sure you want to remove the passkey ":name"? You will no longer be able to use it to sign in.', ['name' => $deletingPasskeyName]) }}
        </x-ui.text>

        <div class="flex justify-end gap-3">
            <x-ui.button variant="outline" x-on:click="$dispatch('close-modal', { id: 'delete-passkey-modal' })">{{ __('Cancel') }}</x-ui.button>
            <x-ui.button variant="danger" wire:click="deletePasskey">{{ __('Remove passkey') }}</x-ui.button>
        </div>
    </div>
</x-ui.modal>
