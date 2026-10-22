<?php

use App\Http\Middleware\UseUserLocale;
use App\Livewire\Portal\Home;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('after login the admin lands on the dashboard and client users on the portal', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('home.redirect'))
        ->assertRedirect(route('dashboard'));

    $this->actingAs(User::factory()->forClient()->create())
        ->get(route('home.redirect'))
        ->assertRedirect(route('portal.home'));
});

test('client users can not reach the admin', function () {
    $this->actingAs(User::factory()->forClient()->create())
        ->get(route('clients.index'))
        ->assertForbidden();
});

test('the admin and stray accounts can not reach the portal', function (User $user) {
    $this->actingAs($user)->get(route('portal.home'))->assertForbidden();
})->with([
    'admin' => fn () => User::factory()->admin()->create(),
    'account without a client' => fn () => User::factory()->create(),
]);

test('the portal lists only the active projects of the user\'s own client', function () {
    $client = Client::factory()->create();
    $user = User::factory()->forClient($client)->create();

    Project::factory()->for($client)->create(['name' => 'Sitio nuevo']);
    Project::factory()->for($client)->delivered()->create(['name' => 'Proyecto viejo']);
    Project::factory()->create(['name' => 'Proyecto de otro cliente']);

    $this->actingAs($user);

    Livewire::test(Home::class)
        ->assertSee('Sitio nuevo')
        ->assertDontSee('Proyecto viejo')
        ->assertDontSee('Proyecto de otro cliente');
});

test('the portal counts what the client still owes', function () {
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create();
    ProjectRequirement::factory()->for($project)->count(2)->create();
    ProjectRequirement::factory()->for($project)->received()->create();

    $this->actingAs(User::factory()->forClient($client)->create(['locale' => 'es']))
        ->get(route('portal.home'))
        ->assertSee('Faltan 2 cosas por entregar');
});

test('the portal speaks the user\'s language', function () {
    $user = User::factory()->forClient()->create(['locale' => 'en']);

    $this->actingAs($user)
        ->get(route('portal.home'))
        ->assertOk()
        ->assertSee('Your projects');
});

test('the portal language also holds on livewire updates', function () {
    expect(Livewire::getPersistentMiddleware())->toContain(UseUserLocale::class);
});

test('project and invoice policies never cross clients', function () {
    $client = Client::factory()->create();
    $user = User::factory()->forClient($client)->create();
    $admin = User::factory()->admin()->create();

    $ownProject = Project::factory()->for($client)->create();
    $otherProject = Project::factory()->create();
    $ownInvoice = Invoice::factory()->create(['client_id' => $client->id]);
    $otherInvoice = Invoice::factory()->create();

    expect(Gate::forUser($user)->allows('view', $ownProject))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $otherProject))->toBeFalse()
        ->and(Gate::forUser($user)->allows('download', $ownInvoice))->toBeTrue()
        ->and(Gate::forUser($user)->allows('download', $otherInvoice))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('view', $otherProject))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('download', $otherInvoice))->toBeTrue();
});

test('only the client team can mark a requirement of their own project as uploaded', function () {
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create();
    $requirement = ProjectRequirement::factory()->for($project)->create();
    $foreignRequirement = ProjectRequirement::factory()->create();

    $user = User::factory()->forClient($client)->create();

    expect(Gate::forUser($user)->allows('markRequirementUploaded', [$project, $requirement]))->toBeTrue()
        ->and(Gate::forUser($user)->allows('markRequirementUploaded', [$project, $foreignRequirement]))->toBeFalse()
        ->and(Gate::forUser(User::factory()->forClient()->create())->allows('markRequirementUploaded', [$project, $requirement]))->toBeFalse();
});

test('an emailed invoice link downloads without logging in', function () {
    config(['filesystems.invoices_disk' => 'r2']);
    Storage::fake('r2');
    Storage::disk('r2')->put('invoices/1/factura.zip', 'zip-bytes');

    $invoice = Invoice::factory()->create(['zip_path' => 'invoices/1/factura.zip', 'number' => 'F-010']);

    $this->get(URL::signedRoute('invoices.download', $invoice))
        ->assertOk()
        ->assertDownload('factura-F-010.zip');
});
