<?php

namespace App\Mail;

use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProposalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Proposal $proposal,
        public string $customMessage = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Proposal {$this->proposal->number} — {$this->proposal->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.proposal',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.proposal', ['proposal' => $this->proposal])
            ->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                "proposal-{$this->proposal->number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
