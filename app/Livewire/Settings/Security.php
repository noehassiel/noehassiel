<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Actions\DeletePasskey;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Security extends Component
{
    use PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    #[Locked]
    public bool $twoFactorEnabled = false;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showVerificationStep = false;

    public string $code = '';

    /** @var list<string> */
    #[Locked]
    public array $recoveryCodes = [];

    /** @var list<array{id: int, name: string, authenticator: ?string, created_at_diff: string, last_used_at_diff: ?string}> */
    #[Locked]
    public array $passkeys = [];

    #[Locked]
    public ?int $deletingPasskeyId = null;

    #[Locked]
    public string $deletingPasskeyName = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $user = Auth::user();

        // A setup that was started but never confirmed is discarded, as in the starter kit
        if (Fortify::confirmsTwoFactorAuthentication() && is_null($user->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication($user);
        }

        $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();
        $this->loadRecoveryCodes();
        $this->loadPasskeys();
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }

    /**
     * Generate a 2FA secret and open the setup modal with its QR code.
     */
    public function startTwoFactorSetup(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $enableTwoFactorAuthentication(Auth::user());

        $user = Auth::user()->fresh();

        try {
            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', __('Failed to fetch setup data.'));
            $this->reset('qrCodeSvg', 'manualSetupKey');
        }

        $this->dispatch('open-modal', id: 'two-factor-setup-modal');
    }

    /**
     * Move the setup modal to the code verification step.
     */
    public function showVerification(): void
    {
        $this->showVerificationStep = true;
        $this->resetErrorBag();
    }

    /**
     * Go back from the verification step to the QR code.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');
        $this->resetErrorBag();
    }

    /**
     * Confirm 2FA with a code from the authenticator app.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate(['code' => ['required', 'string', 'size:6']]);

        $confirmTwoFactorAuthentication(Auth::user(), $this->code);

        $this->twoFactorEnabled = true;
        $this->loadRecoveryCodes();
        $this->closeTwoFactorSetup();
    }

    /**
     * Close the setup modal and clear the setup secrets from the component state.
     */
    public function closeTwoFactorSetup(): void
    {
        $this->reset('code', 'qrCodeSvg', 'manualSetupKey', 'showVerificationStep');
        $this->resetErrorBag();

        $this->dispatch('close-modal', id: 'two-factor-setup-modal');
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disableTwoFactor(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(Auth::user());

        $this->twoFactorEnabled = false;
        $this->recoveryCodes = [];
    }

    /**
     * Replace the user's recovery codes with a new set.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(Auth::user());

        $this->loadRecoveryCodes();
    }

    /**
     * Load the user's passkeys.
     */
    public function loadPasskeys(): void
    {
        $this->passkeys = Auth::user()->passkeys()
            ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
            ->latest()
            ->get()
            ->map(fn ($passkey): array => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'authenticator' => $passkey->authenticator,
                'created_at_diff' => $passkey->created_at->diffForHumans(),
                'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * Open the confirmation modal for removing a passkey.
     */
    public function confirmDeletePasskey(int $passkeyId): void
    {
        $passkey = Auth::user()->passkeys()->findOrFail($passkeyId);

        $this->deletingPasskeyId = $passkey->id;
        $this->deletingPasskeyName = $passkey->name;

        $this->dispatch('open-modal', id: 'delete-passkey-modal');
    }

    /**
     * Remove the passkey awaiting confirmation.
     */
    public function deletePasskey(DeletePasskey $deletePasskey): void
    {
        if (! $this->deletingPasskeyId) {
            return;
        }

        $passkey = Auth::user()->passkeys()->findOrFail($this->deletingPasskeyId);

        $deletePasskey(Auth::user(), $passkey);

        $this->reset('deletingPasskeyId', 'deletingPasskeyName');
        $this->dispatch('close-modal', id: 'delete-passkey-modal');
        $this->loadPasskeys();
    }

    /**
     * Load the decrypted recovery codes when 2FA is enabled.
     */
    private function loadRecoveryCodes(): void
    {
        $user = Auth::user();

        $this->recoveryCodes = $user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes
            ? $user->recoveryCodes()
            : [];
    }

    public function render(): View
    {
        return view('livewire.settings.security');
    }
}
