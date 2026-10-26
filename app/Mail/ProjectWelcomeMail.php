<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the client once the deposit is in: their project exists and the portal is where it lives.
 */
class ProjectWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The invitation token travels only inside this mail; null when the client already has portal access.
     */
    public function __construct(public Project $project, public ?string $invitationToken = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Bienvenido a tu proyecto: {$this->project->name}");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.project-welcome',
            with: [
                'portalUrl' => $this->invitationToken
                    ? route('invitations.accept', $this->invitationToken)
                    : route('login'),
            ],
        );
    }
}
