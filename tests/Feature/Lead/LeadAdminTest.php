<?php

use App\Enums\LeadStatus;
use App\Livewire\Lead\Index;
use App\Livewire\Lead\Show;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('the leads page lists leads with their landing answers', function () {
    Lead::factory()->create(['name' => 'Laura Méndez', 'project_type' => 'ecommerce', 'budget' => '30k_80k']);

    $this->get(route('leads.index'))
        ->assertOk()
        ->assertSee('Laura Méndez')
        ->assertSee('Tienda en línea')
        ->assertSee('$30k – $80k');
});

test('client users can not see the leads', function () {
    $this->actingAs(User::factory()->forClient()->create())
        ->get(route('leads.index'))
        ->assertForbidden();
});

test('leads can be searched and filtered by status', function () {
    Lead::factory()->create(['name' => 'Laura Méndez', 'company' => 'Panadería Luz']);
    Lead::factory()->create(['name' => 'Pedro Ruiz', 'status' => LeadStatus::Lost]);

    Livewire::test(Index::class)
        ->set('search', 'panadería')
        ->assertSee('Laura Méndez')
        ->assertDontSee('Pedro Ruiz')
        ->set('search', '')
        ->set('status', 'lost')
        ->assertSee('Pedro Ruiz')
        ->assertDontSee('Laura Méndez');
});

test('the lead page shows the full request and contact shortcuts', function () {
    $lead = Lead::factory()->create([
        'message' => 'Necesitamos una tienda para vender pan en línea.',
        'phone' => '+52 222 123 4567',
    ]);

    $this->get(route('leads.show', $lead))
        ->assertOk()
        ->assertSee('Necesitamos una tienda para vender pan en línea.')
        ->assertSee('mailto:'.$lead->email, false)
        ->assertSee('https://wa.me/522221234567', false);
});

test('the lead moves through the pipeline and keeps private notes', function () {
    $lead = Lead::factory()->create();

    Livewire::test(Show::class, ['lead' => $lead])
        ->set('status', 'contacted')
        ->set('notes', 'Llamada el martes')
        ->call('saveNotes')
        ->assertHasNoErrors();

    expect($lead->fresh()->status)->toBe(LeadStatus::Contacted)
        ->and($lead->fresh()->notes)->toBe('Llamada el martes');
});

test('won can not be picked by hand, only by converting', function () {
    $lead = Lead::factory()->create();

    Livewire::test(Show::class, ['lead' => $lead])
        ->set('status', 'won')
        ->assertHasErrors('status');

    expect($lead->fresh()->status)->toBe(LeadStatus::New);
});

test('converting a lead creates the client, hands over the proposals and opens the client page', function () {
    $lead = Lead::factory()->create([
        'name' => 'Laura Méndez',
        'company' => 'Panadería Luz',
        'email' => 'laura@panaderialuz.mx',
        'phone' => '+52 222 123 4567',
    ]);
    $proposal = Proposal::factory()->create(['client_id' => null, 'lead_id' => $lead->id]);

    $component = Livewire::test(Show::class, ['lead' => $lead])->call('convert');

    $client = Client::query()->where('email', 'laura@panaderialuz.mx')->sole();
    $lead->refresh();

    expect($client->name)->toBe('Panadería Luz')
        ->and($client->contact_name)->toBe('Laura Méndez')
        ->and($client->phone)->toBe('+52 222 123 4567')
        ->and($lead->status)->toBe(LeadStatus::Won)
        ->and($lead->client_id)->toBe($client->id)
        ->and($lead->converted_at)->not->toBeNull()
        ->and($proposal->fresh()->client_id)->toBe($client->id)
        ->and($proposal->fresh()->lead_id)->toBe($lead->id);

    $component->assertRedirect(route('clients.show', $client));
});

test('converting a lead whose email already belongs to a client links it instead of duplicating', function () {
    $existing = Client::factory()->create(['email' => 'laura@panaderialuz.mx']);
    $lead = Lead::factory()->create(['email' => 'laura@panaderialuz.mx']);

    Livewire::test(Show::class, ['lead' => $lead])->call('convert');

    expect(Client::query()->where('email', 'laura@panaderialuz.mx')->count())->toBe(1)
        ->and($lead->fresh()->client_id)->toBe($existing->id);
});

test('a lead without a company becomes a client named after the person', function () {
    $lead = Lead::factory()->create(['name' => 'Pedro Ruiz', 'company' => null]);

    $client = $lead->convertToClient();

    expect($client->name)->toBe('Pedro Ruiz');
});

test('converting twice does not create a second client', function () {
    $lead = Lead::factory()->create();

    Livewire::test(Show::class, ['lead' => $lead])->call('convert');
    Livewire::test(Show::class, ['lead' => $lead->fresh()])->call('convert');

    expect(Client::count())->toBe(1);
});

test('a converted lead keeps its status even if someone tries to change it', function () {
    $lead = Lead::factory()->create();
    $lead->convertToClient();

    Livewire::test(Show::class, ['lead' => $lead->fresh()])->set('status', 'lost');

    expect($lead->fresh()->status)->toBe(LeadStatus::Won);
});
