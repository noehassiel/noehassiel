<?php

declare(strict_types=1);

use App\Ai\Agents\ProposalDrafter;
use App\Enums\ProposalStatus;
use App\Livewire\Proposal\Create;
use App\Livewire\Proposal\Edit;
use App\Livewire\Proposal\Index;
use App\Livewire\Proposal\Show;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Prompts\AgentPrompt;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
    $this->client = Client::factory()->create();
});

// ─── Index ────────────────────────────────────────────────────────────────────

test('proposals index page loads', function () {
    $this->get(route('proposals.index'))->assertSuccessful()->assertSeeLivewire(Index::class);
});

test('proposals index shows proposals', function () {
    $proposal = Proposal::factory()->create(['client_id' => $this->client->id, 'title' => 'Rediseño web']);

    Livewire::test(Index::class)->assertSee('Rediseño web');
});

test('proposals index search filters', function () {
    Proposal::factory()->create(['client_id' => $this->client->id, 'title' => 'Sitio web corporativo']);
    Proposal::factory()->create(['client_id' => $this->client->id, 'title' => 'App móvil']);

    Livewire::test(Index::class)
        ->set('search', 'corporativo')
        ->assertSee('Sitio web corporativo')
        ->assertDontSee('App móvil');
});

test('proposals index filter by status', function () {
    Proposal::factory()->draft()->create(['client_id' => $this->client->id, 'title' => 'Draft Prop']);
    Proposal::factory()->sent()->create(['client_id' => $this->client->id, 'title' => 'Sent Prop']);

    Livewire::test(Index::class)
        ->set('statusFilter', 'draft')
        ->assertSee('Draft Prop')
        ->assertDontSee('Sent Prop');
});

// ─── Create ───────────────────────────────────────────────────────────────────

test('proposals create page loads', function () {
    $this->get(route('proposals.create'))->assertSuccessful()->assertSeeLivewire(Create::class);
});

test('can create a proposal', function () {
    Livewire::test(Create::class)
        ->set('recipient', "client:{$this->client->id}")
        ->set('title', 'Sitio web premium')
        ->set('projectType', 'website')
        ->set('whatWeDo', 'Diseño y desarrollo de sitio web')
        ->set('howWeDoIt', 'Usando Laravel y Tailwind CSS')
        ->set('stages.0.name', 'Diseño')
        ->set('items.0.description', 'Diseño UI')
        ->set('items.0.quantity', 1)
        ->set('items.0.unit_price', 10000)
        ->call('save')
        ->assertHasNoErrors();

    expect(Proposal::where('title', 'Sitio web premium')->exists())->toBeTrue();
});

test('can create a proposal for a lead', function () {
    $lead = Lead::factory()->create();

    Livewire::test(Create::class)
        ->set('recipient', "lead:{$lead->id}")
        ->set('title', 'Propuesta para prospecto')
        ->set('projectType', 'website')
        ->set('whatWeDo', 'Diseño y desarrollo de sitio web')
        ->set('howWeDoIt', 'Usando Laravel y Tailwind CSS')
        ->set('stages.0.name', 'Diseño')
        ->set('items.0.description', 'Diseño UI')
        ->set('items.0.quantity', 1)
        ->set('items.0.unit_price', 10000)
        ->call('save')
        ->assertHasNoErrors();

    $proposal = Proposal::where('title', 'Propuesta para prospecto')->sole();

    expect($proposal->lead_id)->toBe($lead->id)
        ->and($proposal->client_id)->toBeNull()
        ->and($proposal->recipient()->is($lead))->toBeTrue();
});

test('create proposal rejects a recipient that does not exist', function () {
    Livewire::test(Create::class)
        ->set('recipient', 'lead:999999')
        ->call('save')
        ->assertHasErrors(['recipient']);
});

test('lead proposal show page displays the lead name', function () {
    $lead = Lead::factory()->create(['name' => 'Mariana Prospecto']);
    $proposal = Proposal::factory()->create(['client_id' => null, 'lead_id' => $lead->id]);

    $this->get(route('proposals.show', $proposal))->assertSuccessful()->assertSee('Mariana Prospecto');
});

test('create proposal validates required fields', function () {
    Livewire::test(Create::class)
        ->set('recipient', '')
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['recipient', 'title', 'projectType', 'whatWeDo', 'howWeDoIt']);
});

test('create proposal generates proposal number', function () {
    Livewire::test(Create::class)
        ->set('recipient', "client:{$this->client->id}")
        ->set('title', 'Test Proposal')
        ->set('projectType', 'website')
        ->set('whatWeDo', 'Something')
        ->set('howWeDoIt', 'Somehow')
        ->set('stages.0.name', 'Etapa inicial')
        ->set('items.0.description', 'Item')
        ->set('items.0.quantity', 1)
        ->set('items.0.unit_price', 5000)
        ->call('save')
        ->assertHasNoErrors();

    $proposal = Proposal::where('title', 'Test Proposal')->first();
    expect($proposal->number)->toStartWith('PROP-');
});

test('create proposal calculates subtotals on item change', function () {
    Livewire::test(Create::class)
        ->set('items.0.quantity', 2)
        ->set('items.0.unit_price', 5000)
        ->assertSet('items.0.subtotal', 10000.0);
});

test('proposal stages are saved', function () {
    Livewire::test(Create::class)
        ->set('recipient', "client:{$this->client->id}")
        ->set('title', 'Staged Project')
        ->set('projectType', 'webapp')
        ->set('whatWeDo', 'Build a web app')
        ->set('howWeDoIt', 'Agile methodology')
        ->set('stages.0.name', 'Discovery')
        ->set('stages.0.duration', '1 semana')
        ->set('items.0.description', 'Item')
        ->set('items.0.quantity', 1)
        ->set('items.0.unit_price', 1000)
        ->call('save')
        ->assertHasNoErrors();

    $proposal = Proposal::where('title', 'Staged Project')->first();
    expect($proposal->stages)->toHaveCount(1);
    expect($proposal->stages->first()->name)->toBe('Discovery');
});

// ─── Edit ─────────────────────────────────────────────────────────────────────

test('proposals edit page loads', function () {
    $proposal = Proposal::factory()->create(['client_id' => $this->client->id]);

    $this->get(route('proposals.edit', $proposal))->assertSuccessful()->assertSeeLivewire(Edit::class);
});

test('can update a proposal title', function () {
    $proposal = Proposal::factory()
        ->has(ProposalItem::factory()->count(1), 'items')
        ->create(['client_id' => $this->client->id]);

    Livewire::test(Edit::class, ['proposal' => $proposal])
        ->set('title', 'Updated Title')
        ->call('save')
        ->assertHasNoErrors();

    expect($proposal->fresh()->title)->toBe('Updated Title');
});

// ─── Show ─────────────────────────────────────────────────────────────────────

test('proposals show page loads', function () {
    $proposal = Proposal::factory()->create(['client_id' => $this->client->id]);

    $this->get(route('proposals.show', $proposal))->assertSuccessful()->assertSeeLivewire(Show::class);
});

test('can mark proposal as sent', function () {
    $proposal = Proposal::factory()->draft()->create(['client_id' => $this->client->id]);

    Livewire::test(Show::class, ['proposal' => $proposal])
        ->call('markAsSent');

    expect($proposal->fresh()->status)->toBe(ProposalStatus::Sent);
});

test('can mark proposal as accepted', function () {
    $proposal = Proposal::factory()->sent()->create(['client_id' => $this->client->id]);

    Livewire::test(Show::class, ['proposal' => $proposal])
        ->call('markAsAccepted');

    expect($proposal->fresh()->status)->toBe(ProposalStatus::Accepted);
});

test('can mark proposal as rejected', function () {
    $proposal = Proposal::factory()->sent()->create(['client_id' => $this->client->id]);

    Livewire::test(Show::class, ['proposal' => $proposal])
        ->call('markAsRejected');

    expect($proposal->fresh()->status)->toBe(ProposalStatus::Rejected);
});

// ─── Delete ───────────────────────────────────────────────────────────────────

test('can delete a proposal', function () {
    $proposal = Proposal::factory()->create(['client_id' => $this->client->id]);

    Livewire::test(Index::class)
        ->call('confirmDelete', $proposal->id)
        ->call('delete');

    expect(Proposal::find($proposal->id))->toBeNull();
});

// ─── AI draft ─────────────────────────────────────────────────────────────────

test('ai draft fills the form and leaves every price at zero', function () {
    ProposalDrafter::fake([[
        'title' => 'Sitio web para Café Origen',
        'project_type' => 'website',
        'what_we_do' => 'Un sitio que cuente tu historia.',
        'how_we_do_it' => 'Diseñamos y construimos en Laravel.',
        'includes' => ['Diseño UI', 'Desarrollo'],
        'excludes' => ['Fotografía'],
        'stages' => [
            ['name' => 'Diseño', 'description' => 'Wireframes y UI', 'duration' => '2 semanas'],
            ['name' => 'Desarrollo', 'description' => 'Laravel', 'duration' => '3 semanas'],
        ],
        'items' => [['description' => 'Diseño y desarrollo del sitio', 'quantity' => 1]],
        'open_questions' => ['¿Tienen dominio?'],
    ]]);

    $lead = Lead::factory()->create(['message' => 'Queremos vender café en línea']);

    Livewire::test(Create::class)
        ->set('recipient', "lead:{$lead->id}")
        ->set('draftServices', ['web_design_development'])
        ->set('draftNotes', 'Cafetería en Puebla, quieren sitio con menú y ubicación.')
        ->call('draftWithAi')
        ->assertHasNoErrors()
        ->assertSet('title', 'Sitio web para Café Origen')
        ->assertSet('includesRaw', "Diseño UI\nDesarrollo")
        ->assertCount('stages', 2)
        ->assertSet('items.0.unit_price', 0)
        ->assertSet('openQuestions', ['¿Tienen dominio?']);

    ProposalDrafter::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Queremos vender café en línea')
        && $prompt->contains('Diseño y desarrollo web'));
});

test('ai draft requires notes and a recipient', function () {
    ProposalDrafter::fake();

    Livewire::test(Create::class)
        ->set('recipient', '')
        ->set('draftNotes', '')
        ->call('draftWithAi')
        ->assertHasErrors(['recipient', 'draftNotes']);

    ProposalDrafter::assertNeverPrompted();
});
