<?php

use App\Ai\Agents\ChangeRequestTriage;
use App\Enums\ChangeCategory;
use App\Enums\PortalRole;
use App\Enums\ProjectStatus;
use App\Enums\TicketType;
use App\Jobs\CreateLinearIssueForTicket;
use App\Livewire\Portal\ProjectPreview;
use App\Livewire\Project\Show;
use App\Mail\ProjectApprovedMail;
use App\Mail\TicketOpenedMail;
use App\Models\Client;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Bus::fake([CreateLinearIssueForTicket::class]);
    app()->setLocale('es');

    $this->client = Client::factory()->create();
    $this->user = User::factory()->forClient($this->client)->create(['locale' => 'es']);
    $this->project = Project::factory()->for($this->client)->create([
        'status' => ProjectStatus::Development,
        'preview_url' => 'https://acme.preview.noehassiel.com',
        'revision_rounds' => 2,
    ]);
});

/**
 * @param  array<string, string>  $overrides
 * @return array<string, string>
 */
function triage(array $overrides = []): array
{
    return [
        'action' => 'create',
        'reply' => 'Listo, muevo testimonios antes de servicios.',
        'category' => 'section_order',
        'title' => 'Mover testimonios antes de servicios',
        'description' => 'El cliente pidió: "pon testimonios antes de servicios".',
        ...$overrides,
    ];
}

function previewAs(User $user, Project $project)
{
    return Livewire::actingAs($user)->test(ProjectPreview::class, ['project' => $project]);
}

test('the client opens the preview of their project', function () {
    $this->actingAs($this->user)
        ->get(route('portal.projects.preview', $this->project))
        ->assertOk()
        ->assertSee('https://acme.preview.noehassiel.com')
        ->assertSee('Ronda 1 de 2');
});

test('another client can not open the preview', function () {
    $this->actingAs(User::factory()->forClient()->create())
        ->get(route('portal.projects.preview', $this->project))
        ->assertForbidden();
});

test('an in-scope request becomes a ticket for linear on the current round', function () {
    ChangeRequestTriage::fake([triage()]);

    previewAs($this->user, $this->project)
        ->set('pagePath', '/servicios')
        ->set('message', 'Pon testimonios antes de servicios')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSee('Listo, muevo testimonios antes de servicios.');

    expect(Ticket::sole())
        ->type->toBe(TicketType::Change)
        ->category->toBe(ChangeCategory::SectionOrder)
        ->in_scope->toBeTrue()
        ->round->toBe(1)
        ->page_url->toBe('https://acme.preview.noehassiel.com/servicios');

    Mail::assertSent(TicketOpenedMail::class);
    Bus::assertDispatchedAfterResponse(CreateLinearIssueForTicket::class);
});

test('the triage sees the accepted scope and the rounds left', function () {
    ChangeRequestTriage::fake([triage(['action' => 'answer', 'category' => '', 'title' => '', 'description' => ''])]);
    $this->project->update(['self_service_notes' => 'Banners desde /admin']);

    previewAs($this->user, $this->project)->set('message', '¿Puedo cambiar el banner?')->call('send');

    ChangeRequestTriage::assertPrompted(fn ($prompt) => str_contains($prompt->agent->instructions(), 'round 1 of 2')
        && str_contains($prompt->agent->instructions(), 'Banners desde /admin'));
    expect(Ticket::count())->toBe(0);
});

test('a bug never uses up a round', function () {
    ChangeRequestTriage::fake([triage(['category' => 'bug', 'title' => 'El formulario no envía'])]);

    previewAs($this->user, $this->project)->set('message', 'El formulario no envía')->call('send');

    expect(Ticket::sole())->type->toBe(TicketType::Bug)->round->toBeNull();
});

test('an out-of-scope request waits for the client before going to be quoted', function () {
    ChangeRequestTriage::fake([triage([
        'action' => 'out_of_scope',
        'reply' => 'Un blog no estaba en la propuesta. ¿Quieres que Hassiel lo cotice aparte?',
        'category' => 'feature',
        'title' => 'Agregar blog',
    ])]);

    $component = previewAs($this->user, $this->project)
        ->set('message', 'Agrega un blog')
        ->call('send')
        ->assertSee('Sí, cotízalo');

    expect(Ticket::count())->toBe(0);

    $component->call('confirmQuote');

    expect(Ticket::sole())
        ->title->toBe('[Por cotizar] Agregar blog')
        ->in_scope->toBeFalse()
        ->round->toBeNull();
});

test('declining the quote opens nothing', function () {
    ChangeRequestTriage::fake([triage(['action' => 'out_of_scope', 'category' => 'design'])]);

    previewAs($this->user, $this->project)
        ->set('message', 'Cambia toda la paleta a verde')
        ->call('send')
        ->call('declineQuote')
        ->call('confirmQuote')
        ->assertForbidden();

    expect(Ticket::count())->toBe(0);
});

test('approving the preview opens the balance and tells the admin', function () {
    previewAs($this->user, $this->project)->call('approve')->assertRedirect(route('portal.projects.show', $this->project));

    expect($this->project->fresh())
        ->approved_at->not->toBeNull()
        ->approved_by->toBe($this->user->id)
        ->status->toBe(ProjectStatus::Review);
    Mail::assertSent(ProjectApprovedMail::class);
});

test('a team member without payment access can not approve', function () {
    $member = User::factory()->forClient($this->client)->create([
        'portal_role' => PortalRole::Member,
        'can_view_all_projects' => true,
        'can_view_invoices' => false,
    ]);

    previewAs($member, $this->project)->call('approve')->assertForbidden();
});

test('no more changes can be requested once approved', function () {
    $this->project->approve($this->user);

    previewAs($this->user, $this->project->fresh())
        ->set('message', 'Un cambio más')
        ->call('send')
        ->assertForbidden();
});

test('the admin starts the next round after publishing a new version', function () {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test(Show::class, ['project' => $this->project])
        ->call('startNextRound');

    expect($this->project->fresh()->current_round)->toBe(2);
});

test('the conversation is kept across visits and given to the ai as context', function () {
    ChangeRequestTriage::fake([
        triage(['action' => 'clarify', 'reply' => '¿Cuál imagen, la del inicio o la de nosotros?', 'category' => '', 'title' => '', 'description' => '']),
        triage(['category' => 'image', 'title' => 'Cambiar la imagen del inicio']),
    ]);

    previewAs($this->user, $this->project)->set('message', 'Cambia la imagen')->call('send');

    previewAs($this->user, $this->project)
        ->assertSee('Cambia la imagen')
        ->assertSee('¿Cuál imagen, la del inicio o la de nosotros?')
        ->set('message', 'La del inicio')
        ->call('send');

    ChangeRequestTriage::assertPrompted(fn ($prompt) => count($prompt->agent->history) === 2
        && str_contains($prompt->agent->history[1]['content'], '¿Cuál imagen'));
    expect($this->project->previewMessages()->count())->toBe(4)
        ->and(Ticket::sole()->title)->toBe('Cambiar la imagen del inicio');
});
