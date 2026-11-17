<?php

namespace App\Listeners;

use App\Events\BalancePaid;
use App\Jobs\BringSiteUp;

/**
 * Paid in full: bring the client's site up. Projects without a launch URL are left alone.
 */
class LaunchSiteOnBalancePaid
{
    public function handle(BalancePaid $event): void
    {
        $project = $event->proposal->project;

        if ($project?->launch_url) {
            BringSiteUp::dispatchAfterResponse($project);
        }
    }
}
