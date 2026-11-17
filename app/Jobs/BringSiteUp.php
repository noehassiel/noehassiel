<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Take the client's finished site out of maintenance mode by calling its /_ops/maintenance endpoint.
 * Dispatched after the response (there is no queue worker); the outcome is stored on the project for the admin.
 */
class BringSiteUp
{
    use Dispatchable;

    public function __construct(public Project $project) {}

    public function handle(): void
    {
        $project = $this->project->fresh();

        if (! $project->launch_url || ! $project->launch_token || $project->launched_at) {
            return;
        }

        try {
            Http::withToken($project->launch_token)
                ->acceptJson()
                ->timeout(15)
                ->retry(3, 1000)
                ->post(rtrim($project->launch_url, '/').'/_ops/maintenance', ['action' => 'up'])
                ->throw();
        } catch (Throwable $e) {
            Log::error("Launching project {$project->id} failed: {$e->getMessage()}");
            $project->forceFill(['launch_error' => str($e->getMessage())->limit(240)->toString()])->save();

            return;
        }

        $project->forceFill(['launched_at' => now(), 'launch_error' => null])->save();
    }
}
