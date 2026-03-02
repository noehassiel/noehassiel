<?php

declare(strict_types=1);

use App\Livewire\Client\Index;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
use App\Livewire\Client\Show;
use App\Models\Client;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// ─── Index ────────────────────────────────────────────────────────────────────

test('clients index page loads', function () {
    $this->get(route('clients.index'))->assertSuccessful()->assertSeeLivewire(Index::class);
});

test('clients index shows clients', function () {
    $client = Client::factory()->create(['name' => 'Acme Corp']);

    Livewire::test(Index::class)
        ->assertSee('Acme Corp');
});

test('clients index search filters by name', function () {
    Client::factory()->create(['name' => 'Grupo Innovar']);
    Client::factory()->create(['name' => 'Tech Solutions']);

    Livewire::test(Index::class)
        ->set('search', 'Innovar')
        ->assertSee('Grupo Innovar')
        ->assertDontSee('Tech Solutions');
});

// ─── Create ───────────────────────────────────────────────────────────────────

test('can create a client', function () {
    Livewire::test(Index::class)
        ->call('openCreate')
        ->assertSet('showModal', true)
        ->set('name', 'Nuevo Cliente S.A.')
        ->set('email', 'contacto@nuevo.com')
        ->set('phone', '+52 222 000 0000')
        ->call('save')
        ->assertSet('showModal', false)
        ->assertHasNoErrors();

    expect(Client::where('name', 'Nuevo Cliente S.A.')->exists())->toBeTrue();
});

test('create client requires name', function () {
    Livewire::test(Index::class)
        ->call('openCreate')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('create client validates email format', function () {
    Livewire::test(Index::class)
        ->call('openCreate')
        ->set('name', 'Test Corp')
        ->set('email', 'not-an-email')
        ->call('save')
        ->assertHasErrors(['email' => 'email']);
});

test('create client validates website url', function () {
    Livewire::test(Index::class)
        ->call('openCreate')
        ->set('name', 'Test Corp')
        ->set('website', 'not-a-url')
        ->call('save')
        ->assertHasErrors(['website' => 'url']);
});

// ─── Edit ─────────────────────────────────────────────────────────────────────

test('can edit a client', function () {
    $client = Client::factory()->create(['name' => 'Old Name']);

    Livewire::test(Index::class)
        ->call('openEdit', $client->id)
        ->assertSet('editingId', $client->id)
        ->assertSet('name', 'Old Name')
        ->set('name', 'New Name')
        ->call('save')
        ->assertHasNoErrors();

    expect($client->fresh()->name)->toBe('New Name');
});

// ─── Delete ───────────────────────────────────────────────────────────────────

test('can delete a client', function () {
    $client = Client::factory()->create();

    Livewire::test(Index::class)
        ->call('confirmDelete', $client->id)
        ->assertSet('deletingId', $client->id)
        ->call('delete')
        ->assertSet('deletingId', null);

    expect(Client::find($client->id))->toBeNull();
});

// ─── Show ─────────────────────────────────────────────────────────────────────

test('client show page loads', function () {
    $client = Client::factory()->create();

    $this->get(route('clients.show', $client))
        ->assertSuccessful()
        ->assertSeeLivewire(Show::class);
});

test('client show page displays client info', function () {
    $client = Client::factory()->create([
        'name' => 'Empresa Test',
        'email' => 'test@empresa.com',
        'rfc' => 'EMP901231AB1',
    ]);

    Livewire::test(Show::class, ['client' => $client])
        ->assertSee('Empresa Test')
        ->assertSee('test@empresa.com')
        ->assertSee('EMP901231AB1');
});
