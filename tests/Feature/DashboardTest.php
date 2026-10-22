<?php

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
