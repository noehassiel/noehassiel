<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendingRequirementsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, Project>  $projects  With their pending requirements loaded
     */
    public function __construct(public User $user, public Collection $projects)
    {
        $this->locale($user->locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans_choice('portal.reminder.subject', $this->pendingCount(), ['count' => $this->pendingCount()]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pending-requirements',
            with: ['pendingCount' => $this->pendingCount()],
        );
    }

    public function pendingCount(): int
    {
        return $this->projects->sum(fn (Project $project): int => $project->requirements->count());
    }
}
