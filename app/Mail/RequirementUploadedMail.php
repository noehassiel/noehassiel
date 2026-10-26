<?php

namespace App\Mail;

use App\Models\ProjectRequirement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the admin a client says a deliverable is in their shared folder.
 */
class RequirementUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ProjectRequirement $requirement, public User $markedBy) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->markedBy->email, $this->markedBy->name)],
            subject: "Por revisar: {$this->requirement->title} · {$this->requirement->project->client->name}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.requirement-uploaded');
    }
}
