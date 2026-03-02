<?php

namespace App\Livewire\Client\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Resend\Laravel\Facades\Resend;

class Crud extends Component
{
    use WithFileUploads;

    public Client $client;

    #[Validate('required|string|max:50')]
    public string $number = '';

    #[Validate('required|date')]
    public string $issue_date = '';

    #[Validate('required|string|max:1000')]
    public string $notes = '';

    #[Validate('required|numeric|min:0')]
    public float|string $tax = '';

    #[Validate('required|numeric|min:0')]
    public float|string $total = '';

    #[Validate('required|string|in:MXN,USD')]
    public string $currency = 'MXN';

    #[Validate('required|file|mimes:zip|max:20480')]
    public mixed $zip = null;

    public function mount(Client $client): void
    {
        $this->client = $client;
        $this->issue_date = now()->format('Y-m-d');
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', id: 'invoice-modal');
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->dispatch('close-modal', id: 'invoice-modal');
    }

    public function sendInvoice(): void
    {
        $this->validate();

        $path = $this->zip->store("invoices/{$this->client->id}", 'local');

        $invoice = Invoice::create([
            'client_id' => $this->client->id,
            'number' => $this->number,
            'status' => 'sent',
            'issue_date' => $this->issue_date,
            'due_date' => $this->issue_date,
            'notes' => $this->notes,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'currency' => $this->currency,
            'zip_path' => $path,
        ]);

        $invoice->load('client');

        $html = view('emails.invoice', [
            'invoice' => $invoice,
            'downloadUrl' => \Illuminate\Support\Facades\URL::signedRoute(
                'invoices.download',
                ['invoice' => $invoice],
                now()->addDays(30)
            ),
        ])->render();

        $zipContent = \Illuminate\Support\Facades\Storage::disk('local')->get($path);

        Resend::emails()->send([
            'from' => config('mail.from.name').' <'.config('mail.from.address').'>',
            'to' => [$this->client->email],
            'subject' => "Factura {$invoice->number}",
            'html' => $html,
            'attachments' => [
                [
                    'filename' => "factura-{$invoice->number}.zip",
                    'content' => base64_encode($zipContent),
                ],
            ],
        ]);

        $invoice->update(['sent_at' => now()]);

        $this->resetForm();
        $this->dispatch('close-modal', id: 'invoice-modal');
        $this->client->refresh();

        session()->flash('success', "Factura {$invoice->number} enviada a {$this->client->email}.");
    }

    private function resetForm(): void
    {
        $this->number = '';
        $this->issue_date = now()->format('Y-m-d');
        $this->notes = '';
        $this->tax = '';
        $this->total = '';
        $this->currency = 'MXN';
        $this->zip = null;
        $this->resetValidation();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.client.invoices.crud', [
            'invoices' => $this->client->invoices()->latest()->get(),
        ]);
    }
}
