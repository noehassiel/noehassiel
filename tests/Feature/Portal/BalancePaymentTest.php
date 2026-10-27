<?php

use App\Enums\PaymentMethod;
use App\Enums\PortalRole;
use App\Enums\ProjectStatus;
use App\Events\BalancePaid;
use App\Listeners\HandleStripeCheckoutCompleted;
use App\Livewire\Portal\ProjectShow;
use App\Mail\ProposalResponseMail;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;
use Livewire\Livewire;
use Mockery\MockInterface;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\StripeClient;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    app()->setLocale('es');

    $this->client = Client::factory()->create();
    $this->user = User::factory()->forClient($this->client)->create(['locale' => 'es']);

    $this->proposal = Proposal::factory()->sent()->create([
        'client_id' => $this->client->id,
        'lead_id' => null,
        'subtotal' => 50000,
        'discount' => 0,
        'tax_rate' => 0,
        'total' => 50000,
        'valid_until' => now()->addMonth(),
    ]);
    ProposalItem::factory()->create(['proposal_id' => $this->proposal->id]);
    $this->proposal->accept(false);
    $this->project = $this->proposal->markDepositPaid(PaymentMethod::Transfer, 25000);
});

function fakeStripeSessions(): MockInterface
{
    $sessions = Mockery::mock();
    app()->bind(StripeClient::class, fn () => (object) ['checkout' => (object) ['sessions' => $sessions]]);

    return $sessions;
}

test('the balance is offered only once the project reaches review', function () {
    Livewire::actingAs($this->user)
        ->test(ProjectShow::class, ['project' => $this->project])
        ->assertDontSee('Tu proyecto está listo');

    $this->project->update(['status' => ProjectStatus::Review]);
    $charge = Proposal::cardCharge(25000);

    Livewire::actingAs($this->user)
        ->test(ProjectShow::class, ['project' => $this->project->fresh()])
        ->assertSee('Tu proyecto está listo')
        ->assertSee(['Transferencia', 'Efectivo', 'Tarjeta'])
        ->call('chooseBalancePaymentMethod', 'card')
        ->assertSee(number_format($charge['charge'], 2))
        ->assertSee('meses sin intereses');
});

test('paying the balance by card opens stripe checkout with meses sin intereses', function () {
    $this->project->update(['status' => ProjectStatus::Review]);

    fakeStripeSessions()->shouldReceive('create')
        ->withArgs(fn (array $payload) => data_get($payload, 'payment_method_options.card.installments.enabled') === true
            && data_get($payload, 'metadata.payment') === 'balance')
        ->once()
        ->andReturn(StripeCheckoutSession::constructFrom(['id' => 'cs_test_balance', 'url' => 'https://checkout.stripe.com/c/cs_test_balance']));

    Livewire::actingAs($this->user)
        ->test(ProjectShow::class, ['project' => $this->project->fresh()])
        ->call('payBalanceWithCard')
        ->assertRedirect('https://checkout.stripe.com/c/cs_test_balance');

    expect($this->proposal->fresh()->stripe_balance_checkout_session_id)->toBe('cs_test_balance');
});

test('choosing cash or transfer for the balance tells the admin', function () {
    $this->project->update(['status' => ProjectStatus::Review]);

    Livewire::actingAs($this->user)
        ->test(ProjectShow::class, ['project' => $this->project->fresh()])
        ->call('chooseBalancePaymentMethod', 'cash')
        ->assertSee('entregar la liquidación');

    expect($this->proposal->fresh()->balance_payment_method)->toBe(PaymentMethod::Cash)
        ->and($this->proposal->fresh()->balance_paid_at)->toBeNull();
    Mail::assertSent(ProposalResponseMail::class, fn ($mail) => $mail->event === 'balance_method_chosen');
});

test('the balance can not be paid before review', function () {
    Livewire::actingAs($this->user)
        ->test(ProjectShow::class, ['project' => $this->project])
        ->call('payBalanceWithCard')
        ->assertForbidden();
});

test('a team member without invoice access can not pay the balance', function () {
    $this->project->update(['status' => ProjectStatus::Review]);
    $member = User::factory()->forClient($this->client)->create([
        'portal_role' => PortalRole::Member,
        'can_view_all_projects' => true,
        'can_view_invoices' => false,
    ]);

    Livewire::actingAs($member)
        ->test(ProjectShow::class, ['project' => $this->project->fresh()])
        ->assertSee('La persona a cargo de los pagos')
        ->call('payBalanceWithCard')
        ->assertForbidden();
});

test('a paid balance checkout records the payment and announces it once', function () {
    Event::fake([BalancePaid::class]);
    $this->proposal->forceFill(['stripe_balance_checkout_session_id' => 'cs_test_balance'])->save();
    fakeStripeSessions()->shouldReceive('retrieve')->andReturn(StripeCheckoutSession::constructFrom([
        'id' => 'cs_test_balance',
        'payment_status' => 'paid',
        'amount_total' => 2614567,
        'metadata' => ['proposal_id' => (string) $this->proposal->id],
    ]));

    $webhook = new WebhookReceived(['type' => 'checkout.session.completed', 'data' => ['object' => ['id' => 'cs_test_balance']]]);
    (new HandleStripeCheckoutCompleted)->handle($webhook);
    (new HandleStripeCheckoutCompleted)->handle($webhook);

    expect($this->proposal->fresh())
        ->balance_payment_method->toBe(PaymentMethod::Card)
        ->and((float) $this->proposal->fresh()->balance_paid_amount)->toBe(26145.67);

    Event::assertDispatchedTimes(BalancePaid::class, 1);
    Mail::assertSent(ProposalResponseMail::class, fn ($mail) => $mail->event === 'balance_paid');
});

test('a balance confirmed by the admin is announced too', function () {
    Event::fake([BalancePaid::class]);

    $this->proposal->markBalancePaid(PaymentMethod::Transfer, 25000);
    $this->proposal->markBalancePaid(PaymentMethod::Transfer, 25000);

    Event::assertDispatchedTimes(BalancePaid::class, 1);
});

test('an accepted proposal can no longer be edited', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('proposals.edit', $this->proposal))
        ->assertForbidden();
});
