<?php

namespace App\Livewire\Portal;

use App\Concerns\PasswordValidationRules;
use App\Models\ClientInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class AcceptInvitation extends Component
{
    use PasswordValidationRules;

    #[Locked]
    public string $token = '';

    public string $name = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;

        if ($invitation = ClientInvitation::findPendingByToken($token)) {
            app()->setLocale($invitation->locale);
        }
    }

    /**
     * Create the client user from the invitation and log them into the portal.
     */
    public function accept(): void
    {
        $invitation = ClientInvitation::findPendingByToken($this->token);

        if (! $invitation) {
            $this->addError('invitation', __('portal.invitation.invalid'));

            return;
        }

        app()->setLocale($invitation->locale);

        if (User::query()->where('email', $invitation->email)->exists()) {
            $this->addError('invitation', __('portal.invitation.email_taken'));

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ]);

        $user = DB::transaction(function () use ($invitation, $validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $invitation->email,
                'locale' => $invitation->locale,
                'password' => $validated['password'],
            ]);

            // Not mass assignable on purpose: team membership only comes from an invitation
            $user->forceFill([
                'client_id' => $invitation->client_id,
                'email_verified_at' => now(),
            ])->save();

            $invitation->forceFill(['accepted_at' => now()])->save();

            return $user;
        });

        Auth::login($user);
        Session::regenerate();

        $this->redirectRoute('portal.home');
    }

    public function render(): View
    {
        $invitation = ClientInvitation::findPendingByToken($this->token);

        return view('livewire.portal.accept-invitation', [
            'invitation' => $invitation?->load('client'),
        ])->title(__('portal.invitation.title'));
    }
}
