<?php

namespace App\Livewire\Portal;

use App\Enums\PortalRole;
use App\Mail\ClientInvitationMail;
use App\Models\ClientInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Where a client owner invites teammates and decides what each one sees.
 */
#[Layout('components.layouts.portal')]
class Team extends Component
{
    public string $email = '';

    public string $locale = 'es';

    public bool $allProjects = true;

    /** @var array<int, int|string> */
    public array $projectIds = [];

    public bool $viewInvoices = false;

    /** The member whose access is being edited, if any. */
    public ?int $editingUserId = null;

    public bool $editAllProjects = true;

    /** @var array<int, int|string> */
    public array $editProjectIds = [];

    public bool $editViewInvoices = false;

    public function mount(): void
    {
        $this->authorize('manage-portal-team');
        $this->locale = Auth::user()->locale ?? 'es';
    }

    public function invite(): void
    {
        $this->authorize('manage-portal-team');
        $this->email = strtolower(trim($this->email));

        $this->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'locale' => ['required', Rule::in(['es', 'en'])],
            ...$this->accessRules('allProjects', 'projectIds'),
        ], [
            'email.unique' => __('portal.team.email_taken'),
        ]);

        $client = Auth::user()->client;

        // A fresh invitation replaces any pending one for the same address
        $client->invitations()->pending()->where('email', $this->email)->delete();

        [$invitation, $token] = ClientInvitation::issue($client, $this->email, $this->locale, Auth::user(), [
            'portal_role' => PortalRole::Member,
            'can_view_all_projects' => $this->allProjects,
            'can_view_invoices' => $this->viewInvoices,
            'project_ids' => $this->allProjects ? null : array_map('intval', $this->projectIds),
        ]);

        Mail::to($this->email)->queue(new ClientInvitationMail($invitation, $token));

        $this->reset('email', 'allProjects', 'projectIds', 'viewInvoices');
        $this->dispatch('notify', type: 'success', content: __('portal.team.invited'));
    }

    public function cancelInvitation(int $invitationId): void
    {
        $this->authorize('manage-portal-team');

        Auth::user()->client->invitations()->whereNull('accepted_at')->findOrFail($invitationId)->delete();
    }

    public function editMember(int $userId): void
    {
        $member = $this->member($userId);

        $this->editingUserId = $member->id;
        $this->editAllProjects = $member->can_view_all_projects;
        $this->editProjectIds = $member->projects()->pluck('projects.id')->map(fn ($id) => (string) $id)->all();
        $this->editViewInvoices = $member->can_view_invoices;
        $this->resetValidation();
    }

    public function saveMember(): void
    {
        $member = $this->member($this->editingUserId);

        $this->validate($this->accessRules('editAllProjects', 'editProjectIds'));

        DB::transaction(function () use ($member): void {
            $member->forceFill([
                'can_view_all_projects' => $this->editAllProjects,
                'can_view_invoices' => $this->editViewInvoices,
            ])->save();

            $member->projects()->sync($this->editAllProjects ? [] : array_map('intval', $this->editProjectIds));
        });

        $this->editingUserId = null;
    }

    public function cancelEdit(): void
    {
        $this->editingUserId = null;
        $this->resetValidation();
    }

    /**
     * Remove a member's account, which ends their portal access.
     */
    public function removeMember(int $userId): void
    {
        $this->member($userId)->delete();
        $this->editingUserId = null;
    }

    /**
     * A member of the signed-in owner's team. Owners (including yourself) are managed by noehassiel.
     */
    private function member(?int $userId): User
    {
        $this->authorize('manage-portal-team');

        return Auth::user()->client->users()
            ->where('portal_role', PortalRole::Member)
            ->findOrFail($userId);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function accessRules(string $allField, string $projectsField): array
    {
        $clientProjectIds = Auth::user()->client->projects()->pluck('id')->all();

        return [
            $allField => ['boolean'],
            $projectsField => ["exclude_if:{$allField},true", 'required', 'array', 'min:1'],
            "{$projectsField}.*" => ["exclude_if:{$allField},true", Rule::in($clientProjectIds)],
        ];
    }

    public function render(): View
    {
        $client = Auth::user()->client;

        return view('livewire.portal.team', [
            'members' => $client->users()->with('projects')->orderByRaw("portal_role = 'member'")->orderBy('name')->get(),
            'invitations' => $client->invitations()->pending()->latest()->get(),
            'projects' => $client->projects()->latest()->get(['id', 'name']),
        ])->title(__('portal.team.title'));
    }
}
