<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the admin a client opened a request from the portal, even when Linear isn't set up.
 */
class TicketOpenedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket) {}

    public function envelope(): Envelope
    {
        $user = $this->ticket->user;

        return new Envelope(
            replyTo: $user ? [new Address($user->email, $user->name)] : [],
            subject: "{$this->ticket->project->client->name} · {$this->ticket->type->label()}: {$this->ticket->title}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-opened');
    }
}
