<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            <x-ui.field>
                <x-ui.label>{{ __('Current password') }}</x-ui.label>
                <x-ui.input wire:model="current_password" type="password" required autocomplete="current-password" :revealable="true" />
                <x-ui.error name="current_password" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('New password') }}</x-ui.label>
                <x-ui.input wire:model="password" type="password" required autocomplete="new-password" :revealable="true" />
                <x-ui.error name="password" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>{{ __('Confirm Password') }}</x-ui.label>
                <x-ui.input wire:model="password_confirmation" type="password" required autocomplete="new-password" :revealable="true" />
                <x-ui.error name="password_confirmation" />
            </x-ui.field>

            <div class="flex items-center gap-4">
                <x-ui.button variant="primary" type="submit">{{ __('Save') }}</x-ui.button>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        {{-- Two-factor authentication --}}
        <section class="mt-12 space-y-4">
            <div>
                <x-ui.heading>{{ __('Two-factor authentication') }}</x-ui.heading>
                <x-ui.description>{{ __('Manage your two-factor authentication settings') }}</x-ui.description>
            </div>

            @if ($twoFactorEnabled)
                <x-ui.text>
                    {{ __('You will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                </x-ui.text>

                <x-ui.button variant="danger" wire:click="disableTwoFactor" wire:confirm="{{ __('Disable two-factor authentication?') }}">
                    {{ __('Disable 2FA') }}
                </x-ui.button>

                <div class="space-y-4 rounded-xl border border-zinc-200 p-6 dark:border-white/10" x-data="{ showRecoveryCodes: false }">
                    <div class="space-y-1">
                        <x-ui.heading level="3">{{ __('2FA recovery codes') }}</x-ui.heading>
                        <x-ui.description>
                            {{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
                        </x-ui.description>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-ui.button variant="outline" icon="eye" x-show="! showRecoveryCodes" x-on:click="showRecoveryCodes = true">
                            {{ __('View recovery codes') }}
                        </x-ui.button>
                        <x-ui.button variant="outline" icon="eye-slash" x-show="showRecoveryCodes" x-cloak x-on:click="showRecoveryCodes = false">
                            {{ __('Hide recovery codes') }}
                        </x-ui.button>
                        <x-ui.button variant="ghost" icon="arrow-path" x-show="showRecoveryCodes" x-cloak wire:click="regenerateRecoveryCodes">
                            {{ __('Regenerate codes') }}
                        </x-ui.button>
                    </div>

                    <div x-show="showRecoveryCodes" x-cloak class="space-y-3">
                        <div class="grid gap-1 rounded-lg bg-zinc-100 p-4 font-mono text-sm dark:bg-white/5" role="list" aria-label="{{ __('Recovery codes') }}">
                            @foreach ($recoveryCodes as $recoveryCode)
                                <div role="listitem" class="select-text" wire:loading.class="animate-pulse opacity-50">{{ $recoveryCode }}</div>
                            @endforeach
                        </div>
                        <x-ui.text class="text-xs text-zinc-500">
                            {{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate codes above.') }}
                        </x-ui.text>
                    </div>
                </div>
            @else
                <x-ui.text class="text-zinc-500">
                    {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                </x-ui.text>

                <x-ui.button variant="primary" wire:click="startTwoFactorSetup">{{ __('Enable 2FA') }}</x-ui.button>
            @endif
        </section>

        {{-- Passkeys --}}
        <section class="mt-12 space-y-4">
            <div>
                <x-ui.heading>{{ __('Passkeys') }}</x-ui.heading>
                <x-ui.description>{{ __('Manage your passkeys for passwordless sign-in') }}</x-ui.description>
            </div>

            <div class="overflow-hidden rounded-lg border border-zinc-200 text-sm dark:border-zinc-700">
                @forelse ($passkeys as $passkey)
                    <div wire:key="passkey-{{ $passkey['id'] }}" @class(['flex items-center justify-between p-4', 'border-b border-zinc-200 dark:border-zinc-700' => ! $loop->last])>
                        <div class="flex items-center gap-4">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                                <x-ui.icon name="key" class="size-5 text-zinc-500 dark:text-zinc-400" />
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2.5">
                                    <p class="font-medium">{{ $passkey['name'] }}</p>
                                    @if ($passkey['authenticator'])
                                        <x-ui.badge size="sm">{{ $passkey['authenticator'] }}</x-ui.badge>
                                    @endif
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Added :time', ['time' => $passkey['created_at_diff']]) }}
                                    @if ($passkey['last_used_at_diff'])
                                        <span class="mx-1 opacity-50">/</span>
                                        {{ __('Last used :time', ['time' => $passkey['last_used_at_diff']]) }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <x-ui.button
                            variant="ghost"
                            size="sm"
                            icon="trash"
                            squared
                            aria-label="{{ __('Remove passkey') }}"
                            wire:click="confirmDeletePasskey({{ $passkey['id'] }})"
                            class="text-red-500"
                        />
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800">
                            <x-ui.icon name="key" class="size-7 text-zinc-400 dark:text-zinc-500" />
                        </div>
                        <p class="font-medium">{{ __('No passkeys yet') }}</p>
                        <x-ui.text class="mt-1 !text-center text-zinc-500">{{ __('Add a passkey to sign in without a password') }}</x-ui.text>
                    </div>
                @endforelse
            </div>

            <x-passkey-registration />
        </section>
    </x-settings.layout>

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
</section>
