<x-layouts.auth :title="__('Two-factor authentication')">
    <div
        class="flex flex-col gap-6"
        x-data="{
            showRecoveryInput: @js($errors->has('recovery_code')),
            code: '',
            toggleInput() {
                this.showRecoveryInput = ! this.showRecoveryInput;
                this.code = '';
                this.$nextTick(() => this.showRecoveryInput ? this.$refs.recoveryCode.focus() : this.$dispatch('otp-focus'));
            },
            submitCode(code) {
                this.code = code;
                this.$nextTick(() => this.$refs.form.requestSubmit());
            },
        }"
    >
        <div x-show="! showRecoveryInput">
            <x-auth-header
                :title="__('Authentication code')"
                :description="__('Enter the authentication code provided by your authenticator application.')"
            />
        </div>

        <div x-show="showRecoveryInput" x-cloak>
            <x-auth-header
                :title="__('Recovery code')"
                :description="__('Please confirm access to your account by entering one of your emergency recovery codes.')"
            />
        </div>

        <form method="POST" action="{{ route('two-factor.login.store') }}" x-ref="form" class="flex flex-col gap-6">
            @csrf

            <div x-show="! showRecoveryInput" class="flex flex-col items-center gap-2">
                {{-- The OTP only reports its value on completion, so the code is mirrored into a hidden field --}}
                <x-ui.otp length="6" autofocus x-on:otp-complete="submitCode($event.detail.code)" />
                <input type="hidden" name="code" x-model="code" x-bind:disabled="showRecoveryInput">
                <x-ui.error name="code" />
            </div>

            <div x-show="showRecoveryInput" x-cloak>
                <x-ui.field>
                    <x-ui.label>{{ __('Recovery code') }}</x-ui.label>
                    <x-ui.input
                        name="recovery_code"
                        x-ref="recoveryCode"
                        x-bind:disabled="! showRecoveryInput"
                        autocomplete="one-time-code"
                    />
                    <x-ui.error name="recovery_code" />
                </x-ui.field>
            </div>

            <x-ui.button variant="primary" type="submit" class="w-full">{{ __('Continue') }}</x-ui.button>
        </form>

        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('or you can') }}
            <button type="button" class="cursor-pointer font-medium underline" x-on:click="toggleInput()">
                <span x-show="! showRecoveryInput">{{ __('log in using a recovery code') }}</span>
                <span x-show="showRecoveryInput" x-cloak>{{ __('log in using an authentication code') }}</span>
            </button>
        </div>
    </div>
</x-layouts.auth>
