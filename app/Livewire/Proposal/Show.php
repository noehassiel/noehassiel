<?php

namespace App\Livewire\Proposal;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Resend\Laravel\Facades\Resend;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Show extends Component
{
    public Proposal $proposal;

    public bool $showSendModal = false;

    public string $sendEmail = '';

    public string $sendMessage = '';

    public function mount(Proposal $proposal): void
    {
        $this->proposal = $proposal->load(['client', 'lead', 'stages', 'items']);
        $this->sendEmail = $proposal->recipient()?->email ?? '';
    }

    public function downloadPdf(): StreamedResponse
    {
        $pdf = Pdf::loadView('pdf.proposal', ['proposal' => $this->proposal])
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "proposal-{$this->proposal->number}.pdf"
        );
    }

    public function generatePdfPath(): string
    {
        $pdf = Pdf::loadView('pdf.proposal', ['proposal' => $this->proposal])
            ->setPaper('a4', 'portrait');

        $filename = "proposals/{$this->proposal->number}.pdf";
        Storage::put($filename, $pdf->output());

        $this->proposal->update(['pdf_path' => $filename]);

        return Storage::url($filename);
    }

    public function openSendModal(): void
    {
        $this->showSendModal = true;
        $this->dispatch('open-modal', id: 'send-modal');
    }

    public function closeSendModal(): void
    {
        $this->showSendModal = false;
        $this->resetValidation('sendEmail');
        $this->dispatch('close-modal', id: 'send-modal');
    }

    public function sendByEmail(): void
    {
        $this->validate([
            'sendEmail' => 'required|email',
        ]);

        $html = view('emails.proposal', [
            'proposal' => $this->proposal,
            'customMessage' => $this->sendMessage,
        ])->render();

        $pdfContent = Pdf::loadView('pdf.proposal', ['proposal' => $this->proposal])
            ->setPaper('a4', 'portrait')
            ->output();

        Resend::emails()->send([
            'from' => config('mail.from.name').' <'.config('mail.from.address').'>',
            'to' => [$this->sendEmail],
            'subject' => "Proposal {$this->proposal->number} — {$this->proposal->title}",
            'html' => $html,
            'attachments' => [
                [
                    'filename' => "proposal-{$this->proposal->number}.pdf",
                    'content' => base64_encode($pdfContent),
                ],
            ],
        ]);

        $this->proposal->update([
            'status' => ProposalStatus::Sent,
            'sent_at' => now(),
        ]);

        $this->sendMessage = '';
        $this->showSendModal = false;
        $this->dispatch('close-modal', id: 'send-modal');
        $this->dispatch('toast', message: 'Proposal enviado por correo.', type: 'success');
    }

    public function getWhatsappLink(): string
    {
        $pdfUrl = $this->generatePdfPath();
        $text = urlencode("Hola, te comparto el proposal #{$this->proposal->number}: {$pdfUrl}");

        return "https://wa.me/?text={$text}";
    }

    public function enableSharing(): void
    {
        $this->proposal->enableSharing();
        $this->dispatch('toast', message: 'Link privado creado.', type: 'success');
    }

    public function rotateAccessPassword(): void
    {
        $this->proposal->rotateAccessPassword();
        $this->dispatch('toast', message: 'Nueva contraseña generada. La anterior ya no funciona.', type: 'success');
    }

    public function disableSharing(): void
    {
        $this->proposal->disableSharing();
        $this->dispatch('toast', message: 'Link desactivado.', type: 'warning');
    }

    public function markAsSent(): void
    {
        $this->proposal->update([
            'status' => ProposalStatus::Sent,
            'sent_at' => now(),
        ]);

        $this->dispatch('toast', message: 'Proposal marcado como enviado.', type: 'success');
    }

    public function markAsAccepted(): void
    {
        $this->proposal->update(['status' => ProposalStatus::Accepted]);
        $this->dispatch('toast', message: 'Proposal marcado como aceptado.', type: 'success');
    }

    public function markAsRejected(): void
    {
        $this->proposal->update(['status' => ProposalStatus::Rejected]);
        $this->dispatch('toast', message: 'Proposal marcado como rechazado.', type: 'warning');
    }

    public function render(): View
    {
        return view('livewire.proposal.show')
            ->layout('components.layouts.app.main', ['title' => $this->proposal->number]);
    }
}
