<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Factura {$this->invoice->number}",
        );
    }

    public function content(): Content
    {
        $downloadUrl = URL::signedRoute('invoices.download', ['invoice' => $this->invoice], now()->addDays(30));

        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'downloadUrl' => $downloadUrl,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->invoice->hasZip()) {
            return [];
        }

        $filename = "factura-{$this->invoice->number}.zip";

        return [
            Attachment::fromStorageDisk(Invoice::diskName(), $this->invoice->zip_path)
                ->as($filename)
                ->withMime('application/zip'),
        ];
    }
}
