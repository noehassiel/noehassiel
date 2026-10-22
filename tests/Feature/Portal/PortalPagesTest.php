<?php

use App\Enums\RequirementStatus;
use App\Livewire\Portal\ProjectShow;
use App\Livewire\Portal\Security;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectLink;
use App\Models\ProjectRequirement;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create(['name' => 'Acme']);
    $this->user = User::factory()->forClient($this->client)->create(['locale' => 'es']);
    $this->actingAs($this->user);
});

test('the project page shows progress, checklist and shared folders', function () {
    $project = Project::factory()->for($this->client)->create([
        'name' => 'Sitio Acme',
        'progress_note' => 'Arrancamos el diseño de la home.',
    ]);
    ProjectRequirement::factory()->for($project)->create(['title' => 'Logo en vector']);
    ProjectLink::factory()->for($project)->create(['label' => 'Recursos', 'url' => 'https://www.dropbox.com/scl/fo/abc']);

    $this->get(route('portal.projects.show', $project))
        ->assertOk()
        ->assertSee('Sitio Acme')
        ->assertSee('Arrancamos el diseño de la home.')
        ->assertSee('Logo en vector')
        ->assertSee('Ya lo subí')
        ->assertSee('Dropbox');
});

test('a client can not open another client\'s project', function () {
    $foreignProject = Project::factory()->create();

    $this->get(route('portal.projects.show', $foreignProject))->assertForbidden();
});

test('the client marks a pending item as uploaded', function () {
    $project = Project::factory()->for($this->client)->create();
    $requirement = ProjectRequirement::factory()->for($project)->create();

    Livewire::test(ProjectShow::class, ['project' => $project])
        ->call('markUploaded', $requirement->id)
        ->assertHasNoErrors();

    expect($requirement->fresh()->status)->toBe(RequirementStatus::ClientMarked)
        ->and($requirement->fresh()->client_marked_at)->not->toBeNull();
});

test('a client can not mark an item of another project through their own project', function () {
    $project = Project::factory()->for($this->client)->create();
    $foreignRequirement = ProjectRequirement::factory()->create();

    expect(fn () => Livewire::test(ProjectShow::class, ['project' => $project])->call('markUploaded', $foreignRequirement->id))
        ->toThrow(ModelNotFoundException::class);

    expect($foreignRequirement->fresh()->status)->toBe(RequirementStatus::Pending);
});

test('the invoices page lists only the client\'s own invoices', function () {
    Invoice::factory()->create(['client_id' => $this->client->id, 'number' => 'F-ACME-1']);
    Invoice::factory()->create(['number' => 'F-OTRO-9']);

    $this->get(route('portal.invoices'))
        ->assertOk()
        ->assertSee('F-ACME-1')
        ->assertDontSee('F-OTRO-9');
});

test('a client downloads their own invoice from the portal without a signed link', function () {
    config(['filesystems.invoices_disk' => 'r2']);
    Storage::fake('r2');
    Storage::disk('r2')->put('invoices/acme.zip', 'zip-bytes');

    $invoice = Invoice::factory()->create([
        'client_id' => $this->client->id,
        'number' => 'F-ACME-1',
        'zip_path' => 'invoices/acme.zip',
    ]);

    $this->get(route('portal.invoices.download', $invoice))
        ->assertOk()
        ->assertDownload('factura-F-ACME-1.zip');
});

test('a client can not download another client\'s invoice', function () {
    $foreignInvoice = Invoice::factory()->create(['zip_path' => 'invoices/other.zip']);

    $this->get(route('portal.invoices.download', $foreignInvoice))->assertForbidden();
});

test('the portal security page asks for the password first, then shows passkeys and 2FA', function () {
    $this->get(route('portal.security'))->assertRedirect(route('password.confirm'));

    $this->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('portal.security'))
        ->assertOk()
        ->assertSeeLivewire(Security::class)
        ->assertSee('Activar 2FA');
});

test('a client can change their password from the portal', function () {
    $this->user->update(['password' => Hash::make('password')]);

    Livewire::test(Security::class)
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password', $this->user->fresh()->password))->toBeTrue();
});

test('the passkey well-known endpoint sends each user to their own security page', function () {
    $this->get(route('security.redirect'))->assertRedirect(route('portal.security'));

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('security.redirect'))
        ->assertRedirect(route('settings.security'));
});

test('a client can share a folder on their project', function () {
    $project = Project::factory()->for($this->client)->create();

    Livewire::test(ProjectShow::class, ['project' => $project])
        ->set('linkLabel', 'Fotos y logos')
        ->set('linkUrl', 'https://drive.google.com/drive/folders/abc')
        ->call('addLink')
        ->assertHasNoErrors()
        ->assertSee('Fotos y logos')
        ->assertSee(__('portal.project.added_by_you'));

    expect($project->links()->sole())
        ->added_by->toBe($this->user->id);
});

test('a shared folder must be an https link', function () {
    $project = Project::factory()->for($this->client)->create();

    Livewire::test(ProjectShow::class, ['project' => $project])
        ->set('linkLabel', 'Fotos')
        ->set('linkUrl', 'http://example.com/folder')
        ->call('addLink')
        ->assertHasErrors('linkUrl');

    expect($project->links()->count())->toBe(0);
});

test('a client can remove folders they shared but not the admin ones', function () {
    $project = Project::factory()->for($this->client)->create();
    $own = ProjectLink::factory()->for($project)->create(['added_by' => $this->user->id]);
    $adminLink = ProjectLink::factory()->for($project)->create(['added_by' => null]);

    $component = Livewire::test(ProjectShow::class, ['project' => $project])
        ->call('removeLink', $own->id)
        ->assertHasNoErrors();

    $component->call('removeLink', $adminLink->id)->assertForbidden();

    expect($project->links()->pluck('id')->all())->toBe([$adminLink->id]);
});

test('a client can not add folders to another client\'s project', function () {
    $foreignProject = Project::factory()->create();

    expect($this->user->can('addLink', $foreignProject))->toBeFalse();
});
