<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\Linear;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Mirror a project in Linear: one Linear project plus an issue per proposal stage.
 * Dispatched after the response (there is no queue worker), and safe to run again.
 */
class SyncProjectToLinear
{
    use Dispatchable;

    public function __construct(public Project $project) {}

    public function handle(Linear $linear): void
    {
        $project = $this->project->fresh(['client', 'proposal.stages']);

        if (! $linear->isConfigured() || $project->linear_project_id) {
            return;
        }

        $created = $linear->createProject("{$project->client->name} · {$project->name}", $this->content($project));

        // Save right away so a failure on a stage issue never creates a second Linear project
        $project->forceFill([
            'linear_project_id' => $created['id'],
            'linear_project_url' => $created['url'],
        ])->save();

        foreach ($project->proposal?->stages ?? [] as $stage) {
            $linear->createIssue(
                $stage->name,
                trim(($stage->description ?? '')."\n\n".($stage->duration ? "Duración estimada: {$stage->duration}" : '')),
                $created['id'],
            );
        }
    }

    private function content(Project $project): string
    {
        return collect([
            $project->description,
            '---',
            "**Cliente:** {$project->client->name}".($project->client->email ? " ({$project->client->email})" : ''),
            $project->proposal ? "**Propuesta:** {$project->proposal->number} · {$project->proposal->currency} ".number_format((float) $project->proposal->total, 2) : null,
            '**En la app:** '.route('projects.show', $project),
        ])->filter()->implode("\n\n");
    }
}
