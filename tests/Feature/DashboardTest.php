<?php

use App\Enums\ProjectStatus;
use App\Enums\ProposalStatus;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->admin()->create());

    $this->get('/dashboard')->assertStatus(200);
});

test('non-admin users are forbidden from the admin', function (string $routeName) {
    $this->actingAs(User::factory()->create());

    $this->get(route($routeName))->assertForbidden();
})->with(['dashboard', 'clients.index', 'proposals.index', 'settings.profile']);

test('the dashboard summarizes active projects and pending deliverables', function () {
    $this->actingAs(User::factory()->admin()->create());

    $project = Project::factory()->create(['name' => 'Sitio Innovar', 'status' => ProjectStatus::Design]);
    ProjectRequirement::factory()->for($project)->create(['title' => 'Logo en vectores']);

    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('Sitio Innovar')
        ->assertSee('Logo en vectores');
});

test('the dashboard renders proposals in every status', function (ProposalStatus $status) {
    $this->actingAs(User::factory()->admin()->create());

    Proposal::factory()->create(['status' => $status]);

    $this->get('/dashboard')->assertOk();
})->with(ProposalStatus::cases());
