<?php

declare(strict_types=1);

use App\Enums\PaymentMethod;
use App\Enums\ProposalStatus;
use App\Listeners\HandleStripeCheckoutCompleted;
use App\Livewire\Proposal\Edit;
use App\Livewire\Proposal\Show as ProposalShow;
use App\Livewire\Quote\Show as QuoteShow;
use App\Mail\ProjectWelcomeMail;
use App\Mail\ProposalResponseMail;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;
use Livewire\Livewire;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\StripeClient;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    $this->lead = Lead::factory()->create(['email' => 'mariana@example.com']);
    $this->proposal = Proposal::factory()->sent()->create([
        'client_id' => null,
        'lead_id' => $this->lead->id,
        'subtotal' => 50000,
        'discount' => 0,
        'tax_rate' => 0,
        'total' => 50000,
        'valid_until' => now()->addMonth(),
    ]);
    ProposalItem::factory()->create(['proposal_id' => $this->proposal->id]);
    $this->proposal->enableSharing();
});

function unlockedQuote(Proposal $proposal)
{
    return Livewire::test(QuoteShow::class, ['token' => $proposal->public_token])
        ->set('password', $proposal->access_password)
        ->call('unlock');
}

// ─── Answering ────────────────────────────────────────────────────────────────

test('accepting with an invoice adds IVA to the total and notifies the admin', function () {
    unlockedQuote($this->proposal)
        ->set('requiresInvoice', true)
        ->call('accept')
        ->assertSee('¡Excelente!');

    $this->proposal->refresh();

    expect($this->proposal->status)->toBe(ProposalStatus::Accepted)
        ->and($this->proposal->requires_invoice)->toBeTrue()
        ->and((float) $this->proposal->total)->toBe(58000.0)
        ->and($this->proposal->depositAmount())->toBe(29000.0);

    Mail::assertQueued(ProposalResponseMail::class, fn ($mail) => $mail->event === 'accepted' && $mail->hasTo('admin@noehassiel.com'));
});

test('an invoice does not add IVA twice when the admin already included it', function () {
    $this->proposal->update(['tax_rate' => 16, 'total' => 58000]);

    unlockedQuote($this->proposal)->set('requiresInvoice', true)->call('accept');

    expect((float) $this->proposal->fresh()->total)->toBe(58000.0);
});

test('accepting without an invoice keeps the price without IVA', function () {
    unlockedQuote($this->proposal)->call('accept');

    expect((float) $this->proposal->fresh()->total)->toBe(50000.0);
});

test('requesting changes requires a description and emails it to the admin', function () {
    unlockedQuote($this->proposal)
        ->set('answering', 'changes')
        ->call('requestChanges')
        ->assertHasErrors('response')
        ->set('response', 'Quitar el blog y arrancar en enero')
        ->call('requestChanges')
        ->assertHasNoErrors()
        ->assertSee('Recibí tus comentarios');

    expect($this->proposal->fresh())
        ->status->toBe(ProposalStatus::ChangesRequested)
        ->client_response->toBe('Quitar el blog y arrancar en enero');

    Mail::assertQueued(ProposalResponseMail::class, fn ($mail) => $mail->event === 'changes_requested');
});

test('cancelling notifies the admin', function () {
    unlockedQuote($this->proposal)
        ->set('answering', 'reject')
        ->set('response', 'Ya no tenemos presupuesto')
        ->call('reject');

    expect($this->proposal->fresh()->status)->toBe(ProposalStatus::Rejected);
    Mail::assertQueued(ProposalResponseMail::class, fn ($mail) => $mail->event === 'rejected');
});

test('a locked page cannot answer', function () {
    Livewire::test(QuoteShow::class, ['token' => $this->proposal->public_token])
        ->call('accept')
        ->assertForbidden();

    expect($this->proposal->fresh()->status)->toBe(ProposalStatus::Sent);
});

test('an answered proposal cannot be answered again', function () {
    $component = unlockedQuote($this->proposal);
    $component->call('reject');

    $component->call('accept')->assertForbidden();
});

// ─── Paying ───────────────────────────────────────────────────────────────────

test('choosing transfer shows the bank details and notifies the admin', function () {
    config(['services.bank.clabe' => '012345678901234567']);
    $this->proposal->accept(false);

    unlockedQuote($this->proposal)
        ->call('choosePaymentMethod', 'transfer')
        ->assertSee('012345678901234567');

    expect($this->proposal->fresh()->payment_method)->toBe(PaymentMethod::Transfer);
    Mail::assertQueued(ProposalResponseMail::class, fn ($mail) => $mail->event === 'payment_method_chosen');
});

test('paying by card passes the stripe fee on to the client', function () {
    $charge = Proposal::cardCharge(29000);

    $taxFactor = 1.16;
    $stripeKeeps = round($charge['charge'] * 0.036 * $taxFactor + 3 * $taxFactor, 2);

    expect($charge['charge'] - $stripeKeeps)->toBeGreaterThanOrEqual(29000.0)
        ->and($charge['charge'] - $stripeKeeps)->toBeLessThan(29000.02)
        ->and($charge['fee'])->toBe(round($charge['charge'] - 29000, 2));
});

test('meses sin intereses start at the minimum charge in mxn', function () {
    expect(Proposal::allowsInstallments(3000, 'MXN'))->toBeTrue()
        ->and(Proposal::allowsInstallments(2999.99, 'MXN'))->toBeFalse()
        ->and(Proposal::allowsInstallments(50000, 'USD'))->toBeFalse();
});

test('paying by card enables meses sin intereses in stripe checkout', function () {
    $this->proposal->accept(false);

    $sessions = Mockery::mock();
    $sessions->shouldReceive('create')
        ->withArgs(fn (array $payload) => data_get($payload, 'payment_method_options.card.installments.enabled') === true)
        ->once()
        ->andReturn(StripeCheckoutSession::constructFrom(['id' => 'cs_test_msi', 'url' => 'https://checkout.stripe.com/c/cs_test_msi']));
    app()->bind(StripeClient::class, fn () => (object) ['checkout' => (object) ['sessions' => $sessions]]);

    unlockedQuote($this->proposal)
        ->call('choosePaymentMethod', 'card')
        ->assertSee('meses sin intereses')
        ->call('payWithCard')
        ->assertRedirect('https://checkout.stripe.com/c/cs_test_msi');
});

test('payment options are not available before accepting', function () {
    unlockedQuote($this->proposal)
        ->call('choosePaymentMethod', 'cash')
        ->assertForbidden();
});

// ─── Deposit received → project ───────────────────────────────────────────────

test('admin marking the deposit received creates the project and welcomes the client', function () {
    $this->proposal->accept(true);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(ProposalShow::class, ['proposal' => $this->proposal])
        ->call('markDepositReceived', 'transfer')
        ->assertRedirect();

    $this->proposal->refresh();
    $project = $this->proposal->project;

    expect($this->proposal->status)->toBe(ProposalStatus::Paid)
        ->and((float) $this->proposal->deposit_paid_amount)->toBe(29000.0)
        ->and($project->name)->toBe($this->proposal->title)
        ->and($project->client->email)->toBe('mariana@example.com')
        ->and($this->lead->fresh()->isConverted())->toBeTrue();

    Mail::assertQueued(ProjectWelcomeMail::class, fn ($mail) => $mail->hasTo('mariana@example.com') && $mail->invitationToken !== null);
    Mail::assertQueued(ProposalResponseMail::class, fn ($mail) => $mail->event === 'paid');
});

test('recording the deposit twice creates only one project', function () {
    $this->proposal->accept(false);

    $first = $this->proposal->markDepositPaid(PaymentMethod::Cash, 25000);
    $second = $this->proposal->fresh()->markDepositPaid(PaymentMethod::Cash, 25000);

    expect($second->is($first))->toBeTrue();
    Mail::assertQueuedCount(3); // accepted, paid, welcome
});

test('a client who already has portal access gets a login link instead of an invitation', function () {
    $client = Client::factory()->create();
    User::factory()->create(['client_id' => $client->id]);
    $this->proposal->update(['client_id' => $client->id, 'lead_id' => null]);
    $this->proposal->accept(false);

    $this->proposal->markDepositPaid(PaymentMethod::Cash, 25000);

    Mail::assertQueued(ProjectWelcomeMail::class, fn ($mail) => $mail->invitationToken === null);
});

// ─── Stripe webhook ───────────────────────────────────────────────────────────

function fakeStripeSession(array $session): void
{
    $sessions = Mockery::mock();
    $sessions->shouldReceive('retrieve')->andReturn(StripeCheckoutSession::constructFrom($session));

    app()->bind(StripeClient::class, fn () => (object) ['checkout' => (object) ['sessions' => $sessions]]);
}

function checkoutCompleted(string $sessionId): WebhookReceived
{
    return new WebhookReceived([
        'type' => 'checkout.session.completed',
        'data' => ['object' => ['id' => $sessionId]],
    ]);
}

test('a paid stripe checkout creates the project', function () {
    $this->proposal->accept(false);
    $this->proposal->forceFill(['stripe_checkout_session_id' => 'cs_test_123'])->save();
    fakeStripeSession(['id' => 'cs_test_123', 'payment_status' => 'paid', 'amount_total' => 2614567, 'metadata' => ['proposal_id' => (string) $this->proposal->id]]);

    (new HandleStripeCheckoutCompleted)->handle(checkoutCompleted('cs_test_123'));

    expect($this->proposal->fresh())
        ->status->toBe(ProposalStatus::Paid)
        ->payment_method->toBe(PaymentMethod::Card)
        ->project_id->not->toBeNull();
});

test('a webhook is ignored when stripe says the session is not paid', function () {
    $this->proposal->accept(false);
    $this->proposal->forceFill(['stripe_checkout_session_id' => 'cs_test_456'])->save();
    fakeStripeSession(['id' => 'cs_test_456', 'payment_status' => 'unpaid', 'amount_total' => 2614567, 'metadata' => ['proposal_id' => (string) $this->proposal->id]]);

    (new HandleStripeCheckoutCompleted)->handle(checkoutCompleted('cs_test_456'));

    expect($this->proposal->fresh()->isDepositPaid())->toBeFalse();
});

test('choosing card shows the fee before paying', function () {
    $this->proposal->accept(false);
    $charge = Proposal::cardCharge(25000);

    unlockedQuote($this->proposal)
        ->call('choosePaymentMethod', 'card')
        ->assertSee('Comisión de Stripe')
        ->assertSee(number_format($charge['charge'], 2));

    Mail::assertNotQueued(ProposalResponseMail::class, fn ($mail) => $mail->event === 'payment_method_chosen');
});

test('the client sees the welcome state once the deposit is paid', function () {
    $this->proposal->accept(false);
    $this->proposal->markDepositPaid(PaymentMethod::Cash, 25000);

    unlockedQuote($this->proposal->fresh())->assertSee('Arrancamos.')->assertDontSee('Aceptar propuesta');
});

// ─── History ──────────────────────────────────────────────────────────────────

test('the client sees what they asked for and when, also at the bottom of the page', function () {
    $component = unlockedQuote($this->proposal)
        ->set('answering', 'changes')
        ->set('response', 'Más bajo el precio, por favor')
        ->call('requestChanges');

    $html = $component->html();

    expect(substr_count($html, 'Recibí tus comentarios'))->toBe(2)
        ->and($html)->toContain('Historial de la propuesta')
        ->and($html)->toContain('Pediste cambios')
        ->and($html)->toContain('Enviar más comentarios');
});

test('the client can send more comments while changes are pending', function () {
    $this->proposal->requestChanges('Primero esto');

    unlockedQuote($this->proposal)
        ->set('answering', 'changes')
        ->set('response', 'Y también esto otro')
        ->call('requestChanges')
        ->assertHasNoErrors();

    expect($this->proposal->events()->count())->toBe(2);
});

test('saving an update with a note puts it in the client history and reopens the proposal', function () {
    $this->proposal->requestChanges('Más bajo el precio');

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(Edit::class, ['proposal' => $this->proposal->fresh()])
        ->assertSee('Más bajo el precio')
        ->set('updateNote', 'Bajé el diseño a $4,000.')
        ->call('save')
        ->assertHasNoErrors();

    $this->proposal->refresh();

    expect($this->proposal->status)->toBe(ProposalStatus::Sent)
        ->and($this->proposal->events->first()->body)->toBe('Bajé el diseño a $4,000.');

    unlockedQuote($this->proposal)
        ->assertSee('Actualicé tu propuesta.')
        ->assertSee('Bajé el diseño a $4,000.')
        ->assertSee('Aceptar propuesta');
});

test('saving without a note does not add noise to the history', function () {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test(Edit::class, ['proposal' => $this->proposal])
        ->call('save');

    expect($this->proposal->events()->count())->toBe(0);
});

test('an expired proposal can ask for an update but cannot be accepted', function () {
    $this->proposal->update(['valid_until' => now()->subDay()]);

    unlockedQuote($this->proposal)
        ->assertSee('Pedir actualización')
        ->assertDontSee('Aceptar propuesta')
        ->call('accept')
        ->assertForbidden();

    unlockedQuote($this->proposal)
        ->set('answering', 'changes')
        ->set('response', 'Quiero retomarla para enero')
        ->call('requestChanges')
        ->assertHasNoErrors();
});

test('after accepting, the payment options sit at the bottom with a link from the top', function () {
    $html = unlockedQuote($this->proposal)->call('accept')->html();

    expect(strpos($html, 'Ver formas de pago'))->toBeLessThan(strpos($html, '¡Excelente! Hagámoslo.'))
        ->and(strpos($html, '¡Excelente! Hagámoslo.'))->toBeGreaterThan(strpos($html, 'Inversión'));
});

test('returning from stripe keeps showing the confirmation while the webhook arrives', function () {
    $this->proposal->accept(false);
    $this->proposal->choosePaymentMethod(PaymentMethod::Card);

    unlockedQuote($this->proposal)
        ->set('paymentReturn', 'listo')
        ->call('$refresh')
        ->assertSee('Confirmando tu pago…')
        ->assertDontSee('¡Excelente! Hagámoslo.');
});

test('coming back from stripe confirms the payment without waiting for the webhook', function () {
    $this->proposal->accept(false);
    $this->proposal->forceFill(['payment_method' => PaymentMethod::Card, 'stripe_checkout_session_id' => 'cs_test_789'])->save();
    fakeStripeSession(['id' => 'cs_test_789', 'payment_status' => 'paid', 'amount_total' => 2614567, 'metadata' => ['proposal_id' => (string) $this->proposal->id]]);

    unlockedQuote($this->proposal)
        ->set('paymentReturn', 'listo')
        ->call('$refresh')
        ->assertSee('Arrancamos.');

    expect($this->proposal->fresh()->status)->toBe(ProposalStatus::Paid);
});

// ─── Paid installments ────────────────────────────────────────────────────────

test('paid installments show as paid on the client page', function () {
    $this->proposal->accept(false);
    $this->proposal->markDepositPaid(PaymentMethod::Transfer, 25000);

    unlockedQuote($this->proposal->fresh())
        ->assertSee('✓ Pagado')
        ->assertSee('Al entregar el proyecto completo');
});

test('admin records the balance once the deposit is in', function () {
    $this->proposal->accept(false);
    $this->proposal->markDepositPaid(PaymentMethod::Transfer, 25000);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(ProposalShow::class, ['proposal' => $this->proposal->fresh()])
        ->call('markBalanceReceived', 'cash');

    $proposal = $this->proposal->fresh();

    expect($proposal->balance_payment_method)->toBe(PaymentMethod::Cash)
        ->and((float) $proposal->balance_paid_amount)->toBe(25000.0)
        ->and(array_filter(array_column($proposal->paymentSchedule(), 'paid_at')))->toHaveCount(2);
});

test('the balance cannot be recorded before the deposit', function () {
    $this->proposal->accept(false);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(ProposalShow::class, ['proposal' => $this->proposal])
        ->call('markBalanceReceived', 'cash')
        ->assertForbidden();
});
