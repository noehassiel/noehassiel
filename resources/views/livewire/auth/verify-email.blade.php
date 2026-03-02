<div class="mt-4 flex flex-col gap-6">
    <x-ui.text class="text-center">
        {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
    </x-ui.text>

    @if (session('status') == 'verification-link-sent')
        <x-ui.text class="text-center font-medium text-green-600 dark:text-green-400">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </x-ui.text>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <x-ui.button wire:click="sendVerification" variant="primary" class="w-full">
            {{ __('Resend verification email') }}
        </x-ui.button>

        <x-ui.link class="text-sm cursor-pointer" wire:click="logout">
            {{ __('Log out') }}
        </x-ui.link>
    </div>
</div>
