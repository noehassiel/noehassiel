<?php

declare(strict_types=1);

use App\Livewire\Proposal\Show as ProposalShow;
use App\Livewire\Quote\Show as QuoteShow;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Resend\Contracts\Client as ResendClient;
use Resend\Email;
use Resend\Service\Email as ResendEmailService;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->proposal = Proposal::factory()->create(['title' => 'Rediseño confidencial']);
    $this->proposal->enableSharing();
});

// ─── Admin ────────────────────────────────────────────────────────────────────

test('the share message carries the link, password and the contact first name', function () {
    $this->proposal->client->update(['contact_name' => 'beto ramírez']);

    $message = $this->proposal->fresh()->shareMessage();

    expect($message)
        ->toStartWith('Hola Beto,')
        ->toContain('«Rediseño confidencial»')
        ->toContain($this->proposal->publicUrl())
        ->toContain("Contraseña: {$this->proposal->access_password}");
});

test('the copy message button carries the message to the clipboard handler', function () {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test(ProposalShow::class, ['proposal' => $this->proposal])
        ->assertSeeHtml('x-on:click="copy(')
        ->assertDontSeeHtml('@js(');
});

test('there is no share message without a private link', function () {
    expect(Proposal::factory()->create()->shareMessage())->toBeNull();
});

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

// ─── Sending ──────────────────────────────────────────────────────────────────

test('emailing a proposal sends the private link without attaching the pdf', function () {
    $proposal = Proposal::factory()->draft()->create();

    $emails = Mockery::mock(ResendEmailService::class);
    $emails->shouldReceive('send')
        ->once()
        ->withArgs(function (array $payload) use ($proposal): bool {
            $proposal->refresh();

            return ! isset($payload['attachments'])
                && str_contains($payload['html'], $proposal->publicUrl())
                && ! str_contains($payload['html'], $proposal->access_password);
        })
        ->andReturn(Email::from(['id' => 'fake-id']));

    $resend = Mockery::mock(ResendClient::class);
    $resend->shouldReceive('emails')->andReturn($emails);
    app()->instance('resend', $resend);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(ProposalShow::class, ['proposal' => $proposal])
        ->set('sendEmail', 'cliente@example.com')
        ->call('sendByEmail')
        ->assertHasNoErrors();

    expect($proposal->fresh()->isShared())->toBeTrue();
});

test('the whatsapp message carries the private link but not the password', function () {
    $component = Livewire::actingAs(User::factory()->admin()->create())
        ->test(ProposalShow::class, ['proposal' => $this->proposal]);

    $message = urldecode($component->instance()->whatsappLink());

    expect($message)->toContain($this->proposal->publicUrl())
        ->not->toContain($this->proposal->access_password);
});

// ─── Payment terms ────────────────────────────────────────────────────────────

test('the payment schedule splits the total 50/50 and adds up exactly', function () {
    $proposal = Proposal::factory()->make(['total' => 10000.01]);

    $schedule = $proposal->paymentSchedule();

    expect(array_column($schedule, 'percent'))->toBe([50, 50])
        ->and($schedule[0]['amount'])->toBe(5000.01)
        ->and($schedule[0]['amount'] + $schedule[1]['amount'])->toBe(10000.01);
});

test('the unlocked proposal shows the 50/50 payment terms', function () {
    ProposalItem::factory()->create(['proposal_id' => $this->proposal->id]);
    $this->proposal->update(['subtotal' => 50000, 'discount' => 0, 'tax_rate' => 16, 'total' => 58000]);

    Livewire::test(QuoteShow::class, ['token' => $this->proposal->public_token])
        ->set('password', $this->proposal->access_password)
        ->call('unlock')
        ->assertSee('Forma de pago')
        ->assertSee('Antes de iniciar el proyecto')
        ->assertSee('Al entregar el proyecto completo')
        ->assertSee('29,000.00');
});
