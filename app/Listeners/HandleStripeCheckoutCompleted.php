<?php

namespace App\Listeners;

use App\Models\Proposal;
use Laravel\Cashier\Events\WebhookReceived;

/**
 * A proposal deposit or balance paid by card: record it (a deposit also turns the proposal into a project).
 */
class HandleStripeCheckoutCompleted
{
    private const EVENTS = ['checkout.session.completed', 'checkout.session.async_payment_succeeded'];

    public function handle(WebhookReceived $event): void
    {
        if (! in_array($event->payload['type'] ?? null, self::EVENTS, true)) {
            return;
        }

        $sessionId = $event->payload['data']['object']['id'] ?? null;

        if (! $sessionId) {
            return;
        }

        // Re-checks with Stripe instead of trusting the payload, so a forged webhook can't mark anything paid
        if ($proposal = Proposal::query()->where('stripe_checkout_session_id', $sessionId)->first()) {
            $proposal->syncStripeCheckout();
        } elseif ($proposal = Proposal::query()->where('stripe_balance_checkout_session_id', $sessionId)->first()) {
            $proposal->syncStripeBalanceCheckout();
        }
    }
}
