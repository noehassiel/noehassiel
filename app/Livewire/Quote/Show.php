<?php

namespace App\Livewire\Quote;

use App\Enums\PaymentMethod;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Laravel\Cashier\Checkout;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * The client-facing proposal page: a secret link plus a password.
 */
#[Layout('components.layouts.quote')]
class Show extends Component
{
    private const MAX_ATTEMPTS = 5;

    #[Locked]
    public string $token = '';

    public string $password = '';

    public bool $requiresInvoice = false;

    /** Which answer form is open: null, 'changes' or 'reject'. */
    public ?string $answering = null;

    public string $response = '';

    /** Set to "listo" when Stripe sends the client back, while the webhook confirms the payment. */
    #[Url(as: 'pago')]
    public string $paymentReturn = '';

    public function mount(string $token): void
    {
        abort_unless(Proposal::findByPublicToken($token), 404);

        $this->token = $token;
    }

    public function unlock(): void
    {
        $proposal = $this->proposal();
        $limiterKey = "quote-unlock:{$proposal->id}:".request()->ip();

        if (RateLimiter::tooManyAttempts($limiterKey, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($limiterKey) / 60);
            $this->addError('password', "Demasiados intentos. Intenta de nuevo en {$minutes} min.");

            return;
        }

        if (! $proposal->checkAccessPassword($this->password)) {
            RateLimiter::hit($limiterKey, 600);
            $this->addError('password', 'La contraseña no es correcta.');

            return;
        }

        RateLimiter::clear($limiterKey);
        session()->put($this->sessionKey($proposal), true);
        $this->password = '';

        // Only count the client opening it, not the admin previewing it
        if (! Gate::allows('access-admin')) {
            $proposal->recordView();
        }
    }

    public function accept(): void
    {
        $proposal = $this->answerableProposal();
        abort_unless($proposal->canBeAccepted(), 403);
        $proposal->accept($this->requiresInvoice);
        $this->answering = null;
    }

    public function requestChanges(): void
    {
        $proposal = $this->answerableProposal();
        $this->validate(['response' => 'required|string|min:5|max:5000'], [
            'response.required' => 'Cuéntame qué te gustaría cambiar.',
            'response.min' => 'Cuéntame un poco más para poder ajustarla.',
        ]);

        $proposal->requestChanges($this->response);
        $this->reset('answering', 'response');
    }

    public function reject(): void
    {
        $proposal = $this->answerableProposal();
        $this->validate(['response' => 'nullable|string|max:5000']);

        $proposal->reject($this->response);
        $this->reset('answering', 'response');
    }

    public function choosePaymentMethod(string $method): void
    {
        $proposal = $this->payableProposal();
        $proposal->choosePaymentMethod(PaymentMethod::from($method));
    }

    /** Send the client to Stripe Checkout for the deposit plus the card fee. */
    public function payWithCard(): void
    {
        $proposal = $this->payableProposal();
        $charge = Proposal::cardCharge($proposal->depositAmount());

        $installments = Proposal::allowsInstallments($charge['charge'], $proposal->currency)
            ? ['payment_method_options' => ['card' => ['installments' => ['enabled' => true]]]]
            : [];

        $checkout = Checkout::guest()->create([[
            'price_data' => [
                'currency' => strtolower($proposal->currency),
                'unit_amount' => (int) round($charge['charge'] * 100),
                'product_data' => [
                    'name' => "Anticipo 50% · {$proposal->title}",
                    'description' => "Propuesta {$proposal->number}. Incluye comisión por pago con tarjeta.",
                ],
            ],
        ]], [
            'mode' => 'payment',
            'customer_email' => $proposal->recipient()?->email,
            'metadata' => ['proposal_id' => $proposal->id],
            'success_url' => route('quotes.show', $this->token).'?pago=listo',
            'cancel_url' => route('quotes.show', $this->token),
            ...$installments,
        ]);

        $proposal->forceFill([
            'payment_method' => PaymentMethod::Card,
            'stripe_checkout_session_id' => $checkout->id,
        ])->save();

        $this->redirect($checkout->url);
    }

    public function downloadPdf(): StreamedResponse
    {
        $proposal = $this->proposal();
        abort_unless($this->isUnlocked($proposal), 403);

        $pdf = Pdf::loadView('pdf.proposal', ['proposal' => $proposal->load(['client', 'lead', 'stages', 'items'])])
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "propuesta-{$proposal->number}.pdf"
        );
    }

    /** Re-resolved on every request so disabling or rotating the link takes effect immediately. */
    private function proposal(): Proposal
    {
        $proposal = Proposal::findByPublicToken($this->token);
        abort_unless($proposal, 404);

        return $proposal;
    }

    private function answerableProposal(): Proposal
    {
        $proposal = $this->proposal();
        abort_unless($this->isUnlocked($proposal) && $proposal->isAwaitingAnswer(), 403);

        return $proposal;
    }

    private function payableProposal(): Proposal
    {
        $proposal = $this->proposal();
        abort_unless($this->isUnlocked($proposal) && $proposal->status === ProposalStatus::Accepted, 403);

        return $proposal;
    }

    /** Tied to the token, so rotating the link also locks out existing sessions. */
    private function sessionKey(Proposal $proposal): string
    {
        return "quotes.unlocked.{$proposal->id}.".hash('sha256', $proposal->public_token);
    }

    private function isUnlocked(Proposal $proposal): bool
    {
        return session()->get($this->sessionKey($proposal), false) === true;
    }

    public function render(): View
    {
        $proposal = $this->proposal();
        $isUnlocked = $this->isUnlocked($proposal);

        if ($isUnlocked) {
            $proposal->load(['client', 'lead', 'stages', 'items', 'events']);
        }

        // Back from Stripe: confirm with Stripe directly instead of waiting for the webhook
        if ($isUnlocked && $this->paymentReturn === 'listo' && $proposal->status === ProposalStatus::Accepted) {
            try {
                $proposal->syncStripeCheckout();
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        // While the client decides, totals preview the invoice choice; afterwards they are what was agreed
        $totals = $proposal->isAwaitingAnswer()
            ? $proposal->totalsFor($this->requiresInvoice)
            : $proposal->totalsFor($proposal->requires_invoice);

        $cardCharge = $proposal->status === ProposalStatus::Accepted ? Proposal::cardCharge($proposal->depositAmount()) : null;

        return view('livewire.quote.show', [
            'proposal' => $proposal,
            'totals' => $totals,
            'schedule' => $proposal->paymentSchedule($totals['total']),
            'cardCharge' => $cardCharge,
            'allowsInstallments' => $cardCharge && Proposal::allowsInstallments($cardCharge['charge'], $proposal->currency),
            'bank' => config('services.bank'),
            'isUnlocked' => $isUnlocked,
            'isExpired' => $proposal->valid_until?->endOfDay()->isPast() ?? false,
        ])->title($isUnlocked ? "{$proposal->title} — noehassiel" : 'Propuesta — noehassiel');
    }
}
