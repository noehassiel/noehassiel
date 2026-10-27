<?php

namespace App\Events;

use App\Models\Proposal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * The project is paid in full, by card or confirmed by the admin. Launching to production hangs off this.
 */
class BalancePaid
{
    use Dispatchable, SerializesModels;

    public function __construct(public Proposal $proposal) {}
}
