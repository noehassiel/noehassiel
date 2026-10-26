<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\Linear;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Turn a client's portal request into an issue in the project's Linear project.
 * Dispatched after the response (there is no queue worker), and safe to run again.
 */
class CreateLinearIssueForTicket
{
    use Dispatchable;

    public function __construct(public Ticket $ticket) {}

    public function handle(Linear $linear): void
    {
        $ticket = $this->ticket->fresh(['project.client', 'user']);

        if (! $linear->isConfigured() || $ticket->linear_issue_id) {
            return;
        }

        // A project paid before Linear was set up gets mirrored on its first ticket
        if (! $ticket->project->linear_project_id) {
            SyncProjectToLinear::dispatchSync($ticket->project);
            $ticket->project->refresh();
        }

        $issue = $linear->createIssue(
            "[{$ticket->type->label()}] {$ticket->title}",
            collect([
                $ticket->description,
                '---',
                "**Cliente:** {$ticket->project->client->name}".($ticket->user ? " · reportado por {$ticket->user->name} ({$ticket->user->email})" : ''),
                '**En la app:** '.route('projects.show', $ticket->project),
            ])->implode("\n\n"),
            $ticket->project->linear_project_id,
        );

        $ticket->forceFill([
            'linear_issue_id' => $issue['id'],
            'linear_identifier' => $issue['identifier'],
            'linear_url' => $issue['url'],
        ])->save();
    }
}
