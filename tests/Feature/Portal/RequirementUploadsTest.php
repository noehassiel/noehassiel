<?php

use App\Enums\RequirementStatus;
use App\Livewire\Portal\ProjectShow;
use App\Mail\RequirementUploadedMail;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\RequirementFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake(RequirementFile::diskName());
    Mail::fake();

    $this->client = Client::factory()->create();
    $this->user = User::factory()->forClient($this->client)->create(['locale' => 'es']);
    $this->project = Project::factory()->for($this->client)->create();
    $this->requirement = ProjectRequirement::factory()->for($this->project)->create();
    $this->actingAs($this->user);
});

test('the client uploads files to a deliverable and the admin is notified once', function () {
    Livewire::test(ProjectShow::class, ['project' => $this->project])
        ->set("uploads.{$this->requirement->id}", [UploadedFile::fake()->create('logo.pdf', 200), UploadedFile::fake()->image('foto.jpg')])
        ->call('uploadFiles', $this->requirement->id)
        ->assertHasNoErrors()
        ->set("uploads.{$this->requirement->id}", [UploadedFile::fake()->create('extra.pdf', 10)])
        ->call('uploadFiles', $this->requirement->id);

    $files = $this->requirement->files()->get();

    expect($files)->toHaveCount(3)
        ->and($files->pluck('original_name')->all())->toContain('logo.pdf', 'foto.jpg', 'extra.pdf')
        ->and($files->first()->uploaded_by)->toBe($this->user->id)
        ->and($this->requirement->fresh()->status)->toBe(RequirementStatus::ClientMarked);

    $files->each(fn (RequirementFile $file) => Storage::disk(RequirementFile::diskName())->assertExists($file->path));
    Mail::assertSentCount(1);
    Mail::assertSent(RequirementUploadedMail::class);
});

test('uploads are validated', function (array $uploads) {
    Livewire::test(ProjectShow::class, ['project' => $this->project])
        ->set("uploads.{$this->requirement->id}", $uploads)
        ->call('uploadFiles', $this->requirement->id)
        ->assertHasErrors();

    expect($this->requirement->files()->count())->toBe(0);
})->with([
    'too large' => fn () => [UploadedFile::fake()->create('video.mov', 51201)],
    'too many' => fn () => array_map(fn ($i) => UploadedFile::fake()->create("f{$i}.pdf", 1), range(1, 11)),
]);

test('nothing can be uploaded once the deliverable was received', function () {
    $received = ProjectRequirement::factory()->for($this->project)->received()->create();

    Livewire::test(ProjectShow::class, ['project' => $this->project])
        ->set("uploads.{$received->id}", [UploadedFile::fake()->create('logo.pdf', 10)])
        ->call('uploadFiles', $received->id)
        ->assertForbidden();
});

test('a client removes only their own files and the stored file goes with it', function () {
    $own = RequirementFile::factory()->for($this->requirement, 'requirement')->create(['uploaded_by' => $this->user->id]);
    $teammates = RequirementFile::factory()->for($this->requirement, 'requirement')->create(['uploaded_by' => User::factory()->forClient($this->client)->create()->id]);
    Storage::disk(RequirementFile::diskName())->put($own->path, 'pdf');

    $component = Livewire::test(ProjectShow::class, ['project' => $this->project])->call('removeFile', $own->id);

    expect(RequirementFile::find($own->id))->toBeNull();
    Storage::disk(RequirementFile::diskName())->assertMissing($own->path);

    $component->call('removeFile', $teammates->id)->assertForbidden();
});

test('uploaded files download for the project team and the admin, but not for other clients', function () {
    $file = RequirementFile::factory()->for($this->requirement, 'requirement')->create(['original_name' => 'logo.pdf']);
    Storage::disk(RequirementFile::diskName())->put($file->path, 'pdf');

    $this->get(route('requirement-files.download', $file))->assertOk()->assertDownload('logo.pdf');

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('requirement-files.download', $file))->assertOk();

    $this->actingAs(User::factory()->forClient(Client::factory()->create())->create())
        ->get(route('requirement-files.download', $file))->assertForbidden();
});

test('removing a deliverable deletes its stored files', function () {
    $file = RequirementFile::factory()->for($this->requirement, 'requirement')->create();
    Storage::disk(RequirementFile::diskName())->put($file->path, 'pdf');

    $this->requirement->delete();

    Storage::disk(RequirementFile::diskName())->assertMissing($file->path);
});
