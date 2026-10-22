<?php

namespace App\Livewire\Client;

use App\Mail\ClientInvitationMail;
use App\Models\Client;
use App\Models\ClientInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class PortalUsers extends Component
{
    public Client $client;

    public string $email = '';

    public string $locale = 'es';

    public function openInvite(): void
    {
        $this->reset('email', 'locale');
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'portal-invite-modal');
    }

    /**
     * Email a new portal invitation for this client.
     */
    public function invite(): void
    {
        $this->email = strtolower(trim($this->email));

        $this->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'locale' => ['required', Rule::in(['es', 'en'])],
        ], [
            'email.unique' => 'Ya existe una cuenta con este correo.',
        ]);

        // A fresh invitation replaces any pending one for the same address
        $this->client->invitations()->pending()->where('email', $this->email)->delete();

        $this->sendInvitation($this->email, $this->locale);

        $this->dispatch('close-modal', id: 'portal-invite-modal');
        $this->reset('email', 'locale');
    }

    /**
     * Send a new link for a pending invitation; the old link stops working.
     */
    public function resend(int $invitationId): void
    {
        $invitation = $this->client->invitations()->whereNull('accepted_at')->findOrFail($invitationId);

        $invitation->delete();

        $this->sendInvitation($invitation->email, $invitation->locale);
    }

    public function cancelInvitation(int $invitationId): void
    {
        $this->client->invitations()->whereNull('accepted_at')->findOrFail($invitationId)->delete();
    }

    /**
     * Remove a team member's account, which ends their portal access.
     */
    public function revoke(int $userId): void
    {
        $this->client->users()->findOrFail($userId)->delete();
    }

    private function sendInvitation(string $email, string $locale): void
    {
        [$invitation, $token] = ClientInvitation::issue($this->client, $email, $locale, Auth::user());

        Mail::to($email)->queue(new ClientInvitationMail($invitation, $token));
    }

    public function render(): View
    {
        return view('livewire.client.portal-users', [
            'users' => $this->client->users()->orderBy('name')->get(),
            'invitations' => $this->client->invitations()->whereNull('accepted_at')->latest()->get(),
        ]);
    }
}
