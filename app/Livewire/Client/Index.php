<?php

namespace App\Livewire\Client;

use App\Models\Client;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    // Form state
    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $deletingId = null;

    // Fields
    public string $name = '';

    public string $contactName = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $rfc = '';

    public string $website = '';

    public string $notes = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
        $this->dispatch('open-modal', id: 'client-modal');
    }

    public function openEdit(int $id): void
    {
        $client = Client::findOrFail($id);

        $this->editingId = $id;
        $this->name = $client->name;
        $this->contactName = $client->contact_name ?? '';
        $this->email = $client->email ?? '';
        $this->phone = $client->phone ?? '';
        $this->address = $client->address ?? '';
        $this->rfc = $client->rfc ?? '';
        $this->website = $client->website ?? '';
        $this->notes = $client->notes ?? '';
        $this->showModal = true;
        $this->dispatch('open-modal', id: 'client-modal');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('close-modal', id: 'client-modal');
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'contactName' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'rfc' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $data = [
            'name' => $this->name,
            'contact_name' => $this->contactName ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'rfc' => $this->rfc ?: null,
            'website' => $this->website ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            Client::findOrFail($this->editingId)->update($data);
            $this->dispatch('toast', message: 'Cliente actualizado.', type: 'success');
        } else {
            Client::create($data);
            $this->dispatch('toast', message: 'Cliente creado.', type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('close-modal', id: 'client-modal');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        Client::findOrFail($this->deletingId)->delete();
        $this->deletingId = null;
        $this->dispatch('toast', message: 'Cliente eliminado.', type: 'success');
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->contactName = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->rfc = '';
        $this->website = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function render(): \Illuminate\View\View
    {
        $clients = Client::query()
            ->withCount(['proposals', 'invoices'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('contact_name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.client.index', compact('clients'))
            ->layout('components.layouts.app.main', ['title' => 'Clientes']);
    }
}
