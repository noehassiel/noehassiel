<?php

namespace App\Mail;

use App\Models\ClientInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The plain token travels only inside this mail; the database keeps its hash.
     */
    public function __construct(public ClientInvitation $invitation, public string $token)
    {
        $this->locale($invitation->locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('portal.invitation.mail_subject', ['client' => $this->invitation->client->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-invitation',
            with: ['acceptUrl' => route('invitations.accept', $this->token)],
        );
    }
}
