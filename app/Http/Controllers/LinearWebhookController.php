<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\Linear;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Linear tells us when an issue moves, so tickets in the client portal follow along.
 */
class LinearWebhookController extends Controller
{
    /** Linear's own guidance: reject deliveries older than a minute to block replays. */
    private const MAX_AGE_MS = 60_000;

    public function __invoke(Request $request): Response
    {
        abort_unless(Linear::hasValidSignature($request->getContent(), $request->header('Linear-Signature')), 401);

        $sentAt = (int) $request->input('webhookTimestamp');
        abort_if($sentAt && abs(now()->getTimestampMs() - $sentAt) > self::MAX_AGE_MS, 401);

        $stateType = $request->input('data.state.type');

        if ($request->input('type') === 'Issue' && $stateType) {
            Ticket::query()
                ->where('linear_issue_id', $request->input('data.id'))
                ->first()
                ?->syncLinearState($stateType);
        }

        return response()->noContent();
    }
}
