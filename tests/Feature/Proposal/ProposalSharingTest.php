<?php

declare(strict_types=1);

use App\Livewire\Proposal\Show as ProposalShow;
use App\Livewire\Quote\Show as QuoteShow;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->proposal = Proposal::factory()->create(['title' => 'Rediseño confidencial']);
    $this->proposal->enableSharing();
});

// ─── Admin ────────────────────────────────────────────────────────────────────

test('admin can create a private link for a proposal', function () {
    $proposal = Proposal::factory()->create();

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(ProposalShow::class, ['proposal' => $proposal])
        ->call('enableSharing');

    $proposal->refresh();

    expect($proposal->public_token)->toHaveLength(40)
        ->and($proposal->access_password)->toMatch('/^[a-z2-9]{4}-[a-z2-9]{4}$/');
});

test('the password is stored encrypted', function () {
    $stored = DB::table('proposals')->where('id', $this->proposal->id)->value('access_password');

    expect($stored)->not->toBe($this->proposal->access_password);
});

// ─── Public page ──────────────────────────────────────────────────────────────

test('the public page hides the proposal until the password is entered', function () {
    $this->get($this->proposal->publicUrl())
        ->assertSuccessful()
        ->assertSee('Ver propuesta')
        ->assertDontSee('Rediseño confidencial');
});

test('an unknown token returns 404', function () {
    $this->get(route('quotes.show', str_repeat('a', 40)))->assertNotFound();
});

test('the correct password unlocks the proposal and records the view', function () {
    Livewire::test(QuoteShow::class, ['token' => $this->proposal->public_token])
        ->set('password', '  '.strtoupper($this->proposal->access_password).' ')
        ->call('unlock')
        ->assertHasNoErrors()
        ->assertSee('Rediseño confidencial');

    $this->proposal->refresh();

    expect($this->proposal->view_count)->toBe(1)
        ->and($this->proposal->first_viewed_at)->not->toBeNull();
});

test('a wrong password is rejected', function () {
    Livewire::test(QuoteShow::class, ['token' => $this->proposal->public_token])
        ->set('password', 'nope-nope')
        ->call('unlock')
        ->assertHasErrors('password')
        ->assertDontSee('Rediseño confidencial');
});

test('password attempts are rate limited', function () {
    $component = Livewire::test(QuoteShow::class, ['token' => $this->proposal->public_token]);

    foreach (range(1, 5) as $attempt) {
        $component->set('password', 'nope-nope')->call('unlock');
    }

    $component->set('password', $this->proposal->access_password)
        ->call('unlock')
        ->assertHasErrors('password')
        ->assertDontSee('Rediseño confidencial');
});

test('admin previews do not count as client views', function () {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test(QuoteShow::class, ['token' => $this->proposal->public_token])
        ->set('password', $this->proposal->access_password)
        ->call('unlock');

    expect($this->proposal->fresh()->view_count)->toBe(0);
});

test('rotating the password locks out the old one', function () {
    $oldPassword = $this->proposal->access_password;
    $this->proposal->rotateAccessPassword();

    Livewire::test(QuoteShow::class, ['token' => $this->proposal->public_token])
        ->set('password', $oldPassword)
        ->call('unlock')
        ->assertHasErrors('password');
});

test('disabling sharing makes the link return 404', function () {
    $url = $this->proposal->publicUrl();
    $this->proposal->disableSharing();

    $this->get($url)->assertNotFound();
});

test('the pdf cannot be downloaded before unlocking', function () {
    Livewire::test(QuoteShow::class, ['token' => $this->proposal->public_token])
        ->call('downloadPdf')
        ->assertForbidden();
});
