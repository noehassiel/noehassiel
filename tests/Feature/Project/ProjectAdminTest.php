<?php

use App\Enums\ProjectStatus;
use App\Enums\RequirementStatus;
use App\Livewire\Client\Projects;
use App\Livewire\Project\Show;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectLink;
use App\Models\ProjectRequirement;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());

    // Livewire::test skips route middleware, so apply the admin's language by hand
    app()->setLocale('es');
});

test('the client page lists its projects', function () {
    $client = Client::factory()->create();
    Project::factory()->for($client)->create(['name' => 'Tienda en línea']);

    $this->get(route('clients.show', $client))
        ->assertOk()
        ->assertSeeLivewire(Projects::class)
        ->assertSee('Tienda en línea');
});

test('a project can be created from the client page', function () {
    $client = Client::factory()->create();

    $component = Livewire::test(Projects::class, ['client' => $client])
        ->set('name', 'Sitio corporativo')
        ->set('status', 'design')
        ->call('create')
        ->assertHasNoErrors();

    $project = $client->projects()->sole();

    expect($project->name)->toBe('Sitio corporativo')
        ->and($project->status)->toBe(ProjectStatus::Design);

    $component->assertRedirect(route('projects.show', $project));
});

test('the project page renders with status labels in spanish', function () {
    $project = Project::factory()->create(['status' => ProjectStatus::Development]);

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('Desarrollo');
});

test('client users can not open the admin project page', function () {
    $project = Project::factory()->create();

    $this->actingAs(User::factory()->forClient($project->client)->create())
        ->get(route('projects.show', $project))
        ->assertForbidden();
});

test('project details can be updated', function () {
    $project = Project::factory()->create();

    Livewire::test(Show::class, ['project' => $project])
        ->set('status', 'review')
        ->set('progressNote', 'Listo para tu revisión')
        ->set('startsOn', '2026-10-01')
        ->set('dueOn', '2026-11-15')
        ->call('saveDetails')
        ->assertHasNoErrors()
        ->assertDispatched('project-saved');

    $project->refresh();

    expect($project->status)->toBe(ProjectStatus::Review)
        ->and($project->progress_note)->toBe('Listo para tu revisión')
        ->and($project->due_on->toDateString())->toBe('2026-11-15');
});

test('the delivery date can not be before the start date', function () {
    Livewire::test(Show::class, ['project' => Project::factory()->create()])
        ->set('startsOn', '2026-11-15')
        ->set('dueOn', '2026-10-01')
        ->call('saveDetails')
        ->assertHasErrors('dueOn');
});

test('shared folders can be added, are recognised and can be removed', function () {
    $project = Project::factory()->create();

    $component = Livewire::test(Show::class, ['project' => $project])
        ->set('linkLabel', 'Fotos')
        ->set('linkUrl', 'https://acme.sharepoint.com/sites/marketing/fotos')
        ->call('addLink')
        ->assertHasNoErrors()
        ->assertSee('SharePoint');

    $link = $project->links()->sole();

    $component->call('removeLink', $link->id);

    expect($project->links()->count())->toBe(0);
});

test('only https links are accepted as shared folders', function () {
    Livewire::test(Show::class, ['project' => Project::factory()->create()])
        ->set('linkLabel', 'Fotos')
        ->set('linkUrl', 'javascript:alert(1)')
        ->call('addLink')
        ->assertHasErrors('linkUrl');
});

test('providers are detected from the folder url', function (string $url, string $provider) {
    expect((new ProjectLink(['url' => $url]))->provider())->toBe($provider);
})->with([
    ['https://drive.google.com/drive/folders/abc', 'google_drive'],
    ['https://acme-my.sharepoint.com/personal/x', 'sharepoint'],
    ['https://1drv.ms/f/s!abc', 'onedrive'],
    ['https://www.dropbox.com/scl/fo/abc', 'dropbox'],
    ['https://notdropbox.com/abc', 'other'],
]);

test('requirements are added at the end of the checklist', function () {
    $project = Project::factory()->create();
    ProjectRequirement::factory()->for($project)->create(['position' => 4]);

    Livewire::test(Show::class, ['project' => $project])
        ->set('requirementTitle', 'Logo en vector')
        ->call('addRequirement')
        ->assertHasNoErrors();

    $requirement = $project->requirements()->where('title', 'Logo en vector')->sole();

    expect($requirement->position)->toBe(5)
        ->and($requirement->status)->toBe(RequirementStatus::Pending);
});

test('a requirement the client marked as uploaded can be received or sent back', function () {
    $project = Project::factory()->create();
    $requirement = ProjectRequirement::factory()->for($project)->create();
    $requirement->markUploadedByClient();

    $component = Livewire::test(Show::class, ['project' => $project])
        ->assertSee('Por revisar')
        ->call('markPending', $requirement->id);

    expect($requirement->fresh()->status)->toBe(RequirementStatus::Pending)
        ->and($requirement->fresh()->client_marked_at)->toBeNull();

    $component->call('markReceived', $requirement->id);

    expect($requirement->fresh()->status)->toBe(RequirementStatus::Received)
        ->and($requirement->fresh()->received_at)->not->toBeNull();
});

test('a received requirement stays received if the client marks it again', function () {
    $requirement = ProjectRequirement::factory()->received()->create();

    $requirement->markUploadedByClient();

    expect($requirement->fresh()->status)->toBe(RequirementStatus::Received);
});

test('items of another project can not be changed from this project page', function () {
    $project = Project::factory()->create();
    $foreignRequirement = ProjectRequirement::factory()->create();

    expect(fn () => Livewire::test(Show::class, ['project' => $project])->call('markReceived', $foreignRequirement->id))
        ->toThrow(ModelNotFoundException::class);

    expect($foreignRequirement->fresh()->status)->toBe(RequirementStatus::Pending);
});

test('deleting a project removes its checklist and folders and returns to the client', function () {
    $project = Project::factory()->create();
    ProjectRequirement::factory()->for($project)->create();
    ProjectLink::factory()->for($project)->create();

    Livewire::test(Show::class, ['project' => $project])
        ->call('deleteProject')
        ->assertRedirect(route('clients.show', $project->client));

    expect(Project::find($project->id))->toBeNull()
        ->and(ProjectRequirement::count())->toBe(0)
        ->and(ProjectLink::count())->toBe(0);
});
