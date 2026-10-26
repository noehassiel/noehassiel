<?php

use App\Enums\PortalRole;
use App\Livewire\Portal\AcceptInvitation;
use App\Livewire\Portal\Team;
use App\Mail\ClientInvitationMail;
use App\Models\Client;
use App\Models\ClientInvitation;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    $this->client = Client::factory()->create();
    $this->owner = User::factory()->forClient($this->client)->create(['locale' => 'es']);
    $this->projectA = Project::factory()->for($this->client)->create(['name' => 'Proyecto A']);
    $this->projectB = Project::factory()->for($this->client)->create(['name' => 'Proyecto B']);
});

function member(Client $client, array $access = [], array $projects = []): User
{
    $user = User::factory()->forClient($client)->create();
    $user->forceFill(['portal_role' => PortalRole::Member, 'can_view_all_projects' => true, 'can_view_invoices' => false, ...$access])->save();
    $user->projects()->sync($projects);

    return $user;
}

// ─── Inviting ─────────────────────────────────────────────────────────────────

test('an owner invites a member with access to selected projects only', function () {
    $this->actingAs($this->owner);

    Livewire::test(Team::class)
        ->set('email', 'Ana@Bonkers.test')
        ->set('allProjects', false)
        ->set('projectIds', [(string) $this->projectA->id])
        ->set('viewInvoices', false)
        ->call('invite')
        ->assertHasNoErrors();

    $invitation = ClientInvitation::sole();

    expect($invitation)
        ->email->toBe('ana@bonkers.test')
        ->portal_role->toBe(PortalRole::Member)
        ->can_view_all_projects->toBeFalse()
        ->project_ids->toBe([$this->projectA->id]);

    Mail::assertSent(ClientInvitationMail::class);
});

test('choosing selected projects requires at least one', function () {
    $this->actingAs($this->owner);

    Livewire::test(Team::class)
        ->set('email', 'ana@bonkers.test')
        ->set('allProjects', false)
        ->call('invite')
        ->assertHasErrors('projectIds');
});

test('an owner cannot grant projects from another client', function () {
    $this->actingAs($this->owner);
    $foreign = Project::factory()->create();

    Livewire::test(Team::class)
        ->set('email', 'ana@bonkers.test')
        ->set('allProjects', false)
        ->set('projectIds', [(string) $foreign->id])
        ->call('invite')
        ->assertHasErrors('projectIds.0');
});

test('accepting a member invitation applies its access', function () {
    [$invitation, $token] = ClientInvitation::issue($this->client, 'ana@bonkers.test', 'es', $this->owner, [
        'portal_role' => PortalRole::Member,
        'can_view_all_projects' => false,
        'can_view_invoices' => false,
        'project_ids' => [$this->projectB->id],
    ]);

    Livewire::test(AcceptInvitation::class, ['token' => $token])
        ->set('name', 'Ana')
        ->set('password', 'una-contraseña-segura-123')
        ->set('password_confirmation', 'una-contraseña-segura-123')
        ->call('accept');

    $ana = User::where('email', 'ana@bonkers.test')->sole();

    expect($ana->isPortalOwner())->toBeFalse()
        ->and($ana->canSeeProject($this->projectB))->toBeTrue()
        ->and($ana->canSeeProject($this->projectA))->toBeFalse()
        ->and($ana->canSeeInvoices())->toBeFalse();
});

test('admin invitations still create owners', function () {
    [$invitation] = ClientInvitation::issue($this->client, 'boss@bonkers.test');

    expect($invitation->fresh()->portal_role)->toBe(PortalRole::Owner);
});

// ─── What members see ─────────────────────────────────────────────────────────

test('a restricted member only sees and opens their projects', function () {
    $this->actingAs(member($this->client, ['can_view_all_projects' => false], [$this->projectA->id]));

    $this->get(route('portal.home'))->assertSee('Proyecto A')->assertDontSee('Proyecto B');
    $this->get(route('portal.projects.show', $this->projectA))->assertSuccessful();
    $this->get(route('portal.projects.show', $this->projectB))->assertForbidden();
});

test('a member without invoice access cannot see or download invoices', function () {
    $invoice = Invoice::factory()->for($this->client)->create();
    $this->actingAs(member($this->client, ['can_view_invoices' => false]));

    $this->get(route('portal.home'))->assertDontSee(__('portal.nav.invoices'));
    $this->get(route('portal.invoices'))->assertForbidden();
    expect(auth()->user()->can('download', $invoice))->toBeFalse();
});

test('members cannot manage the team', function () {
    $this->actingAs(member($this->client, ['can_view_invoices' => true]));

    $this->get(route('portal.team'))->assertForbidden();
});

// ─── Editing and removing ─────────────────────────────────────────────────────

test('an owner changes what a member can see', function () {
    $ana = member($this->client);
    $this->actingAs($this->owner);

    Livewire::test(Team::class)
        ->call('editMember', $ana->id)
        ->set('editAllProjects', false)
        ->set('editProjectIds', [(string) $this->projectB->id])
        ->set('editViewInvoices', true)
        ->call('saveMember')
        ->assertHasNoErrors();

    $ana->refresh();

    expect($ana->canSeeProject($this->projectA))->toBeFalse()
        ->and($ana->canSeeProject($this->projectB))->toBeTrue()
        ->and($ana->canSeeInvoices())->toBeTrue();
});

test('an owner removes a member but not another owner', function () {
    $ana = member($this->client);
    $otherOwner = User::factory()->forClient($this->client)->create();
    $this->actingAs($this->owner);

    Livewire::test(Team::class)->call('removeMember', $ana->id);
    expect(User::find($ana->id))->toBeNull();

    expect(fn () => Livewire::test(Team::class)->call('removeMember', $otherOwner->id))
        ->toThrow(ModelNotFoundException::class);
});
