<?php

namespace App\Mail;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the admin what a client did on a proposal: accepted, asked for changes, cancelled, chose how to pay, or paid.
 */
class ProposalResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public const SUBJECTS = [
        'accepted' => 'aceptó la propuesta',
        'changes_requested' => 'pidió cambios',
        'rejected' => 'canceló la propuesta',
        'payment_method_chosen' => 'eligió cómo pagar el anticipo',
        'paid' => 'pagó el anticipo',
    ];

    /**
     * @param  'accepted'|'changes_requested'|'rejected'|'payment_method_chosen'|'paid'  $event
     */
    public function __construct(public Proposal $proposal, public string $event) {}

    public function envelope(): Envelope
    {
        $recipient = $this->proposal->recipient();

        return new Envelope(
            replyTo: $recipient?->email ? [new Address($recipient->email, $recipient->name)] : [],
            subject: "{$recipient?->name} ".self::SUBJECTS[$this->event]." · {$this->proposal->number}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.proposal-response');
    }
}
