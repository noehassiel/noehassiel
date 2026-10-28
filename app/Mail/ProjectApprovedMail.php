<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the admin the client approved the preview; the balance payment is now open to them.
 */
class ProjectApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Project $project) {}

    public function envelope(): Envelope
    {
        $approver = $this->project->approver;

        return new Envelope(
            replyTo: $approver ? [new Address($approver->email, $approver->name)] : [],
            subject: "{$this->project->client->name} aprobó el proyecto · {$this->project->name}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.project-approved');
    }
}
