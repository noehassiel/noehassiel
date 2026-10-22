<?php

namespace App\Livewire\Client;

use App\Enums\PortalRole;
use App\Mail\ClientInvitationMail;
use App\Models\Client;
use App\Models\ClientInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class PortalUsers extends Component
{
    public Client $client;

    public string $email = '';

    public string $locale = 'es';

    /** The portal user whose role and access are being edited. */
    public ?int $editingUserId = null;

    public string $editRole = 'owner';

    public bool $editAllProjects = true;

    /** @var array<int, int|string> */
    public array $editProjectIds = [];

    public bool $editViewInvoices = true;

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

        // Keep what the invitation granted, so a re-sent member invite doesn't come back as an owner
        $this->sendInvitation($invitation->email, $invitation->locale, [
            'portal_role' => $invitation->portal_role,
            'can_view_all_projects' => $invitation->can_view_all_projects,
            'can_view_invoices' => $invitation->can_view_invoices,
            'project_ids' => $invitation->project_ids,
        ]);
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

    public function editAccess(int $userId): void
    {
        $user = $this->client->users()->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->editRole = $user->portal_role?->value ?? PortalRole::Owner->value;
        $this->editAllProjects = $user->can_view_all_projects;
        $this->editProjectIds = $user->projects()->pluck('projects.id')->map(fn ($id) => (string) $id)->all();
        $this->editViewInvoices = $user->can_view_invoices;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'portal-access-modal');
    }

    /**
     * Change someone's role or, for members, which projects and whether invoices they see.
     */
    public function saveAccess(): void
    {
        $user = $this->client->users()->findOrFail($this->editingUserId);
        $isMember = $this->editRole === PortalRole::Member->value;

        $this->validate([
            'editRole' => ['required', Rule::enum(PortalRole::class)],
            'editAllProjects' => ['boolean'],
            'editProjectIds' => [Rule::excludeIf(! $isMember || $this->editAllProjects), 'required', 'array', 'min:1'],
            'editProjectIds.*' => [Rule::excludeIf(! $isMember || $this->editAllProjects), Rule::in($this->client->projects()->pluck('id')->all())],
        ], [
            'editProjectIds.required' => 'Elige al menos un proyecto.',
        ]);

        DB::transaction(function () use ($user, $isMember): void {
            $user->forceFill([
                'portal_role' => $this->editRole,
                // Owners always see everything; keep their flags open so a later downgrade starts from full access
                'can_view_all_projects' => ! $isMember || $this->editAllProjects,
                'can_view_invoices' => ! $isMember || $this->editViewInvoices,
            ])->save();

            $user->projects()->sync($isMember && ! $this->editAllProjects ? array_map('intval', $this->editProjectIds) : []);
        });

        $this->editingUserId = null;
        $this->dispatch('close-modal', id: 'portal-access-modal');
    }

    /**
     * @param  array<string, mixed>  $access
     */
    private function sendInvitation(string $email, string $locale, array $access = []): void
    {
        [$invitation, $token] = ClientInvitation::issue($this->client, $email, $locale, Auth::user(), array_filter($access, fn ($value) => $value !== null));

        Mail::to($email)->queue(new ClientInvitationMail($invitation, $token));
    }

    public function render(): View
    {
        return view('livewire.client.portal-users', [
            'users' => $this->client->users()->with('projects:id,name')->orderBy('name')->get(),
            'projects' => $this->client->projects()->latest()->get(['id', 'name']),
            'invitations' => $this->client->invitations()->whereNull('accepted_at')->latest()->get(),
        ]);
    }
}
