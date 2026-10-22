<?php

use App\Enums\PortalRole;
use App\Livewire\Client\PortalUsers;
use App\Livewire\Portal\AcceptInvitation;
use App\Mail\ClientInvitationMail;
use App\Models\Client;
use App\Models\ClientInvitation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    $this->client = Client::factory()->create(['name' => 'Acme']);
});

test('the admin can invite someone to a client\'s portal', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(PortalUsers::class, ['client' => $this->client])
        ->set('email', 'Ana@Acme.com')
        ->set('locale', 'en')
        ->call('invite')
        ->assertHasNoErrors();

    $invitation = $this->client->invitations()->sole();

    expect($invitation->email)->toBe('ana@acme.com')
        ->and($invitation->locale)->toBe('en');

    Mail::assertQueued(ClientInvitationMail::class, fn (ClientInvitationMail $mail) => $mail->hasTo('ana@acme.com')
        && $mail->locale === 'en'
        && $invitation->token_hash === ClientInvitation::hashToken($mail->token));
});

test('an email that already has an account can not be invited', function () {
    User::factory()->create(['email' => 'ana@acme.com']);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(PortalUsers::class, ['client' => $this->client])
        ->set('email', 'ana@acme.com')
        ->call('invite')
        ->assertHasErrors('email');

    Mail::assertNothingQueued();
});

test('resending an invitation invalidates the previous link', function () {
    [$invitation, $oldToken] = ClientInvitation::issue($this->client, 'ana@acme.com');

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(PortalUsers::class, ['client' => $this->client])->call('resend', $invitation->id);

    expect(ClientInvitation::findPendingByToken($oldToken))->toBeNull()
        ->and($this->client->invitations()->pending()->count())->toBe(1);
    Mail::assertQueued(ClientInvitationMail::class);
});

test('accepting an invitation creates a client user and opens the portal', function () {
    [, $token] = ClientInvitation::issue($this->client, 'ana@acme.com', 'en');

    $this->get(route('invitations.accept', $token))->assertOk()->assertSee('Acme');

    Livewire::test(AcceptInvitation::class, ['token' => $token])
        ->set('name', 'Ana López')
        ->set('password', 'a-strong-password')
        ->set('password_confirmation', 'a-strong-password')
        ->call('accept')
        ->assertHasNoErrors()
        ->assertRedirect(route('portal.home'));

    $user = User::query()->where('email', 'ana@acme.com')->sole();

    expect($user->client_id)->toBe($this->client->id)
        ->and($user->locale)->toBe('en')
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and(ClientInvitation::findPendingByToken($token))->toBeNull();

    $this->assertAuthenticatedAs($user);
});

test('an invitation can only be used once', function () {
    [, $token] = ClientInvitation::issue($this->client, 'ana@acme.com');

    $accept = fn () => Livewire::test(AcceptInvitation::class, ['token' => $token])
        ->set('name', 'Ana')
        ->set('password', 'a-strong-password')
        ->set('password_confirmation', 'a-strong-password')
        ->call('accept');

    $accept();
    auth()->logout();

    $accept()->assertHasErrors('invitation');

    expect(User::query()->where('email', 'ana@acme.com')->count())->toBe(1);
});

test('an expired invitation is rejected', function () {
    [, $token] = ClientInvitation::issue($this->client, 'ana@acme.com');

    $this->travel(ClientInvitation::EXPIRES_IN_DAYS + 1)->days();

    Livewire::test(AcceptInvitation::class, ['token' => $token])
        ->set('name', 'Ana')
        ->set('password', 'a-strong-password')
        ->set('password_confirmation', 'a-strong-password')
        ->call('accept')
        ->assertHasErrors('invitation');

    $this->assertGuest();
});

test('the admin can revoke a team member, ending their access', function () {
    $member = User::factory()->forClient($this->client)->create();

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(PortalUsers::class, ['client' => $this->client])->call('revoke', $member->id);

    expect(User::find($member->id))->toBeNull();
});

test('a user of another client can not be revoked from this client\'s screen', function () {
    $stranger = User::factory()->forClient()->create();

    $this->actingAs(User::factory()->admin()->create());

    expect(fn () => Livewire::test(PortalUsers::class, ['client' => $this->client])->call('revoke', $stranger->id))
        ->toThrow(ModelNotFoundException::class);

    expect(User::find($stranger->id))->not->toBeNull();
});

test('admin sees and changes a portal user role and access', function () {
    $project = Project::factory()->for($this->client)->create(['name' => 'Sitio web']);
    $user = User::factory()->forClient($this->client)->create();

    Livewire::test(PortalUsers::class, ['client' => $this->client])
        ->assertSee('Administrador')
        ->call('editAccess', $user->id)
        ->set('editRole', 'member')
        ->set('editAllProjects', false)
        ->set('editProjectIds', [(string) $project->id])
        ->set('editViewInvoices', false)
        ->call('saveAccess')
        ->assertHasNoErrors()
        ->assertSee('Sitio web')
        ->assertSee('sin facturas');

    $user->refresh();

    expect($user->isPortalOwner())->toBeFalse()
        ->and($user->canSeeProject($project))->toBeTrue()
        ->and($user->canSeeInvoices())->toBeFalse();
});

test('resending a member invitation keeps its access', function () {
    [$invitation] = ClientInvitation::issue($this->client, 'ana@acme.test', 'es', null, [
        'portal_role' => PortalRole::Member,
        'can_view_invoices' => false,
    ]);

    Livewire::test(PortalUsers::class, ['client' => $this->client])->call('resend', $invitation->id);

    expect(ClientInvitation::sole())
        ->portal_role->toBe(PortalRole::Member)
        ->can_view_invoices->toBeFalse();
});
