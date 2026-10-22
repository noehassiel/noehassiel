<?php

use App\Enums\ProjectStatus;
use App\Livewire\Portal\ProjectShow;
use App\Mail\PendingRequirementsMail;
use App\Mail\RequirementUploadedMail;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

/**
 * A client with one team member and one ongoing project that has the given requirements.
 *
 * @return array{0: Client, 1: User, 2: Project}
 */
function clientWithPendingWork(int $pending = 1, int $received = 0, ProjectStatus $status = ProjectStatus::Development): array
{
    $client = Client::factory()->create();
    $user = User::factory()->forClient($client)->create(['locale' => 'en']);
    $project = Project::factory()->for($client)->create(['status' => $status]);

    ProjectRequirement::factory()->for($project)->count($pending)->create();
    ProjectRequirement::factory()->for($project)->received()->count($received)->create();

    return [$client, $user, $project];
}

test('each team member gets one reminder listing only pending items, in their language', function () {
    [$client, $user, $project] = clientWithPendingWork(pending: 2, received: 1);
    $teammate = User::factory()->forClient($client)->create(['locale' => 'es']);

    $this->artisan('portal:send-reminders')->assertSuccessful();

    Mail::assertQueued(PendingRequirementsMail::class, 2);
    Mail::assertQueued(PendingRequirementsMail::class, fn (PendingRequirementsMail $mail) => $mail->hasTo($user->email)
        && $mail->locale === 'en'
        && $mail->pendingCount() === 2);
    Mail::assertQueued(PendingRequirementsMail::class, fn (PendingRequirementsMail $mail) => $mail->hasTo($teammate->email)
        && $mail->locale === 'es');

    expect($client->fresh()->reminded_at)->not->toBeNull();
});

test('a client is not reminded again until the interval has passed', function () {
    config(['app.portal_reminder_days' => 3]);
    clientWithPendingWork();

    $this->artisan('portal:send-reminders');
    $this->travel(2)->days();
    $this->artisan('portal:send-reminders');

    Mail::assertQueued(PendingRequirementsMail::class, 1);

    $this->travel(1)->days();
    $this->artisan('portal:send-reminders');

    Mail::assertQueued(PendingRequirementsMail::class, 2);
});

test('no reminder goes out when nothing is pending, the project is paused or delivered, or nobody has access', function () {
    clientWithPendingWork(pending: 0, received: 2);
    clientWithPendingWork(status: ProjectStatus::Paused);
    clientWithPendingWork(status: ProjectStatus::Delivered);

    $clientWithoutUsers = Client::factory()->create();
    ProjectRequirement::factory()->for(Project::factory()->for($clientWithoutUsers))->create();

    $this->artisan('portal:send-reminders')->assertSuccessful();

    Mail::assertNothingQueued();
});

test('items the client already marked as uploaded are not reminded', function () {
    [, , $project] = clientWithPendingWork(pending: 1);
    $project->requirements()->first()->markUploadedByClient();

    $this->artisan('portal:send-reminders');

    Mail::assertNothingQueued();
});

test('the reminder email renders the pending items and a link to the portal', function () {
    [, $user, $project] = clientWithPendingWork();
    $requirement = $project->requirements()->first();

    $mail = new PendingRequirementsMail($user, $project->client->projects()->with('requirements')->get());

    $mail->assertSeeInHtml($requirement->title)
        ->assertSeeInHtml(route('portal.home'))
        ->assertSeeInHtml('Go to my portal');
});

test('the admin is notified when a client marks an item as uploaded', function () {
    [, $user, $project] = clientWithPendingWork();
    $requirement = $project->requirements()->first();

    $this->actingAs($user);

    $component = Livewire::test(ProjectShow::class, ['project' => $project])
        ->call('markUploaded', $requirement->id);

    Mail::assertQueued(RequirementUploadedMail::class, fn (RequirementUploadedMail $mail) => $mail->hasTo(config('app.admin_email'))
        && $mail->requirement->is($requirement));

    $component->call('markUploaded', $requirement->id);

    Mail::assertQueued(RequirementUploadedMail::class, 1);
});

test('the admin notification links to the project page', function () {
    [, $user, $project] = clientWithPendingWork();

    (new RequirementUploadedMail($project->requirements()->first(), $user))
        ->assertSeeInHtml(route('projects.show', $project))
        ->assertSeeInHtml($user->email);
});
