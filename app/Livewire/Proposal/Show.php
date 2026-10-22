<?php

namespace App\Livewire\Proposal;

use App\Enums\PaymentMethod;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $this->proposal = $proposal->load(['client', 'lead', 'stages', 'items', 'events']);
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

        // The email only carries the private link; the PDF lives behind the password
        if (! $this->proposal->isShared()) {
            $this->proposal->enableSharing();
        }

        $html = view('emails.proposal', [
            'proposal' => $this->proposal,
            'customMessage' => $this->sendMessage,
        ])->render();

        Resend::emails()->send([
            'from' => config('mail.from.name').' <'.config('mail.from.address').'>',
            'to' => [$this->sendEmail],
            'subject' => "Propuesta {$this->proposal->number} — {$this->proposal->title}",
            'html' => $html,
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

    /** Shares the private link only; send the password in a separate message. */
    public function whatsappLink(): ?string
    {
        if (! $this->proposal->isShared()) {
            return null;
        }

        $text = urlencode("Hola, te comparto la propuesta {$this->proposal->number}: {$this->proposal->publicUrl()}\nEn un momento te mando la contraseña.");

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

    /** For transfers and cash: the admin confirms the deposit arrived, which creates the project. */
    public function markDepositReceived(string $method): void
    {
        abort_unless(in_array($this->proposal->status, [ProposalStatus::Sent, ProposalStatus::Accepted, ProposalStatus::ChangesRequested], true), 403);

        $project = $this->proposal->markDepositPaid(PaymentMethod::from($method), $this->proposal->depositAmount());

        $this->dispatch('toast', message: 'Anticipo registrado. Proyecto creado y correo de bienvenida enviado.', type: 'success');
        $this->redirectRoute('projects.show', $project, navigate: true);
    }

    public function markBalanceReceived(string $method): void
    {
        abort_unless($this->proposal->isDepositPaid() && ! $this->proposal->balance_paid_at, 403);

        $this->proposal->markBalancePaid(PaymentMethod::from($method), $this->proposal->balanceAmount());
        $this->dispatch('toast', message: 'Liquidación registrada. El proyecto queda pagado por completo.', type: 'success');
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
