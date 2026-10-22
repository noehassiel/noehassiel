<?php

declare(strict_types=1);

use App\Enums\ProposalStatus;
use App\Livewire\Proposal\Create;
use App\Livewire\Proposal\Edit;
use App\Livewire\Proposal\Index;
use App\Livewire\Proposal\Show;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        ->set('clientId', $this->client->id)
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

test('create proposal validates required fields', function () {
    Livewire::test(Create::class)
        ->set('clientId', 0)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['clientId', 'title', 'projectType', 'whatWeDo', 'howWeDoIt']);
});

test('create proposal generates proposal number', function () {
    Livewire::test(Create::class)
        ->set('clientId', $this->client->id)
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
        ->set('clientId', $this->client->id)
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
